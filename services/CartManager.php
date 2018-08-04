<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.modules.carts.components.CartData");
Yii::import("common.modules.campaigns.components.DiscountData");
/**
 * Description of CartManager
 * 
 * SCENARIO 1: Cart with multi-shops - all shops in cart should have same currency
 * SCENARIO 2: Cart with single-shop - if one shop one cart, shop currency can not need to tied to cart/site currency.
 * 
 * Current implementation is that one cart single-shop (but ActiveCart supports both scenario 1 and 2)
 * and cart checkout currency will always follow shop currency
 * 
 * @TODO Improvement: auto currency conversion can be implemented if support multi-shop multi-currency.
 * 
 *                       
 * @author kwlok
 */
class CartManager extends ServiceManager 
{
    /**
     * Shipping address zone validation.
     * Currently validation fields: country
     * 
     * @var array
     */
    public $shippingZoneScope = ['country'];
    /**
     * Indicate if want to save transition into db (s_transition)
     * 
     * @var boolean
     */
    public $saveTransition = false;
    /**
     * Indicate if want to keep record of completed cart checkout (s_cart with status CHK;CFM;
     * 
     * @var boolean True to keep; False to delete db record after successful checkout
     */
    public $keepRecord = true;
    
    public function init() 
    {
        parent::init();
    }
    /**
     * Add Cart items
     * 
     * @param type $cart
     * @param CartItemForm $item
     * @return boolean True if successful
     * @throws CException
     */
    public function add(ActiveCart $cart,$item) 
    {
        //Check if shopping cart is full
        if ($cart->getShop($item->shop_id)->getCartItemsLimit()==$cart->getCount($item->shop_id)){
            logWarning('Shopping cart is full, maximum '.$cart->getShop($item->shop_id)->getCartItemsLimit().' items only.',$item->getAttributes());
            throw new CException(Sii::t('sii','Shopping Cart is full, maximum {max} items only.',['{max}'=>$cart->getShop($item->shop_id)->getCartItemsLimit()]));
        }

        if (!$item->getCheckout())
            throw new CException(Sii::t('sii','Cart item is not checkout'));

        //Add cart will check if item currency is same as shop currency, if not, reject add cart. 
        if ($item->currency!=Shop::model()->findByPk($item->shop_id)->currency)
            throw new CException(Sii::t('sii','We do not accept currency {symbol} in this cart',['{symbol}'=>$form->currency]));
        
        //Here use cart->put() instead of cart->update()
        $cart->put($item,$item->quantity);

        //Add campaign product if any
        if ($item->hasCampaign()){
            if ($item->hasCampaignBgaG()){
                $promotion = $this->_constructCampaignItem($item);
                if (!$promotion->validate()){
                    logError(__METHOD__.' promotion item erros',$promotion->errors,false);
                    throw new CException(Sii::t('sii','Failed to validate promotion product'));
                }
                if ($cart->existsItem($promotion->getKey()))
                    throw new CException(Sii::t('sii','This promotion item already exists in cart'));
                $cart->put($promotion,$promotion->quantity);
            }
            else {
                $item->setCampaignItem(true);
            }
        }

        if (!$cart->onPreview){
            //insert db record (s_cart)
            foreach ($cart->getItems() as $item){
                $model = $this->_loadItem($item,[Helper::getGuest(),Helper::getVisitor()]);
                if ($model===null) {  
                    $this->_insertItem($item);
                }
                else {
                    $this->_updateItem($model, $item);
                }
            }
            //Track cart visit
            $this->getAnalyticManager()->trackAddCartVisit($item->shopModel->account_id, $item->shopModel->id, Helper::getVisitor());
        }
        else {
            logInfo(__METHOD__.' Cart on PREVIEW skip inserting db record!');
        }
        
        logInfo(__METHOD__.' cart count '.count($cart->getItems()).' ok');

        return true;
    }
    /**
     * Checkout cart items 
     *
     * @param type $user
     * @param ActiveCart $cart
     * @param type $shop
     * @return boolean
     * @throws CException
     */
    public function checkout($user,ActiveCart $cart,$shop)
    {
        //nullify cart session
        $cart->clearSessionVariables(false);//do not clear campaign data

        //checkout shop
        $cart->setCheckoutShop($shop); 
        
        //identify user: either login user or guest
        $user = $this->parseCheckoutUser($user);
        
        if (!$cart->onPreview){
            
            //update all session items shopper to correct user id (from guest to real user)
            foreach ($cart->getItems($shop) as $item){
                $model = $this->_loadItem($item,$user);
                if ($model===null){
                    $this->_insertItem($item);
                    logTrace(__METHOD__.' cart item not found! insert new one',$item->attributes);            
                }
                else {
                    logTrace(__METHOD__." change user from $model->shopper to $user",$model->attributes);            
                    $model->shopper = $user;
                    $model->update();
                }
            }        

            //start workflow transition
            $this->_runWorkflowBatch(
                    $user,
                    $cart->getCheckoutItems($shop), 
                    __FUNCTION__, 
                    'Checkout by '.$user, 
                    true);

            //Track cart visit
            $this->getAnalyticManager()->trackCheckoutVisit($cart->getShop($shop)->account_id, $shop, Helper::getVisitor());
        
            logInfo(__METHOD__.' ok');

        }
        else {
            logInfo(__METHOD__.' Cart on PREVIEW skip update cart db session');
        }

        return true;
    }
    /**
     * Paypal express checkout cart items 
     *
     * @param type $user
     * @param ActiveCart $cart
     * @param type $shop
     * @return string Paypal express checkout url
     * @throws CException
     */
    public function paypalExpressCheckout($user,$cart,$shop,$override='0')
    {
        //[1] Perform a internal checkout first
        if ($this->checkout($user, $cart, $shop)!=true)
            throw new CException(Sii::t('sii','Checkout Error'));
        //[2] Peform paypal express checkout
        $checkoutData = $this->getPayPalExpress()->prepareCheckoutData($cart,$override);
        $result = $this->getPayPalExpress()->expressCheckout($cart->getShop($shop),$checkoutData);
        $cart->setPaypalExpressResponse($result);//store result of expressCheckout
        //[3] Return paypal express checkout url
        return $this->getPayPalExpress()->paypalUrl.urldecode($result["TOKEN"]); 
    }
    /**
     * Paypal express checkout cart items 
     *
     * @param type $user
     * @param ActiveCart $cart
     * @param type $shop
     * @return string Paypal express checkout url
     * @throws CException
     */
    public function paypalExpressReview($cart,$token)
    {
        $paypalResponse = $cart->getPayPalExpressResponse();//retrieve response of setExpressCheckout
        if ($token == $paypalResponse['TOKEN']){
            //[1] Get previous checkout data details
            $checkoutData = $this->getPayPalExpress()->getCheckoutData($cart->getShop($cart->getCheckoutShop()),$token);
            //[2] fill up shipping address return from Paypal
            $addressForm = new CartAddressForm();
            foreach ($this->getPayPalExpress()->getShippingAddress($checkoutData) as $key => $value)
                $addressForm->$key = $value;
            $cart->setShippingAddress($addressForm);
            //[3] fill up payment form with necessary information (mainly payment_method and paypalResponse
            $paymentForm = new CartPaymentMethodForm();
            $paymentForm->shop_id = $cart->getCheckoutShop();
            $paymentForm->method = PaymentMethod::PAYPAL_EXPRESS_CHECKOUT;
            $paymentForm->method_desc = $paymentForm->getMethodName($paymentForm->method);
            $paymentForm->id = $paymentForm->getPaymentMethod($paymentForm->method)->id;
            $paymentForm->extraPaymentData = $checkoutData;
            $cart->setPaymentMethod($paymentForm);
            logTrace(__METHOD__,$cart->getPaymentMethod()->getAttributes());
            return true;
        }
        else
            throw new CException(Sii::t('sii','Invalid Token'));
        
    }
    /**
     * Fill in shipping address for cart items 
     * Sequence of events: 
     * [1] Validate shipping address: performs a check on shipping address vs shop supported shipping zone
     *     If shipping zone does not match with shipping address, reject add cart
     * [2] Start cart workflow
     * [3] Return true if successful
     * 
     * @param type $user
     * @param ActiveCart $cart
     * @param CartAddressForm $form
     * @return boolean True if successful
     * @throws CException
     */
    public function fillShippingAddress($user,ActiveCart $cart,CartAddressForm $form)
    {
        $checkoutShop = $cart->getCheckoutShop();
        if (!isset($checkoutShop))
            throw new CException(Sii::t('sii','Checkout shop not found'));
        
        //Validate shipping address
        if (!$form->validate()){
            logError('shipping address form validation error', $form->getErrors(), false);
            throw new CException(Helper::htmlErrors($form->getErrors()));
        }
        //set shipping address
        $cart->setShippingAddress($form);

        //Validate shipping zone
        $shippingZoneErrors = $cart->validateShippingZone($checkoutShop,$this->shippingZoneScope,user()->getLocale());
        if (count($shippingZoneErrors)>0){
            logWarning(__METHOD__.' Out of shipping zone', $shippingZoneErrors);
            throw new CException(Helper::htmlList($shippingZoneErrors));
        }

        //identify user: either login user or guest
        $user = $this->parseCheckoutUser($user);
        
        if (!$cart->onPreview){
            //start workflow transition
            $this->_runWorkflowBatch(
                    $user,
                    $cart->getCheckoutItems($checkoutShop),
                    __FUNCTION__,
                    'Shipping address filled in by '.$user);
            logInfo(__METHOD__.' ok');
        }
        else {
            logInfo(__METHOD__.' Cart on PREVIEW skip update cart db session');
        }

        return true;
    }
    /**
     * Select payment method for cart items 
     *
     * @param type $user
     * @param ActiveCart $cart
     * @param CartPaymentMethodForm $form
     * @return boolean True if successful
     * @throws CException
     */
    public function selectPaymentMethod($user,ActiveCart $cart,CartPaymentMethodForm $form)
    {
        $checkoutShop = $cart->getCheckoutShop();
        if (!isset($checkoutShop))
            throw new CException(Sii::t('sii','Checkout shop not found'));

        //validate payment form
        if (!$form->validate()){
            logError('payment form validation error', $form->getErrors(), false);
            throw new CException(Sii::t('sii','Invalid payment method'));
        }

        $cart->setPaymentMethod($form);
        
        if (!$cart->onPreview){
            //set cart model payment method
            foreach ($cart->getCheckoutItems($checkoutShop) as $item){
                $model = $this->_loadItem($item,$user);
                $model->payment_method = $form->getPaymentMethodData();
                //logTrace(__METHOD__.' update cart model payment_method',$model->attributes);
                $model->update();
            }        

            //identify user: either login user or guest
            $user = $this->parseCheckoutUser($user);

            //start workflow transition
            $this->_runWorkflowBatch(
                    $user,
                    $cart->getCheckoutItems($checkoutShop), 
                    __FUNCTION__, 
                    'Payment method '.$form->method_desc.' selected by '.$user);

            logInfo(__METHOD__.' ok');
        }
        else {
            logInfo(__METHOD__.' Cart on PREVIEW skip update cart db session');
        }

        return true;
    }
    /**
     * Confirm cart items
     * Sequence of events: 
     * [1] Construct order form 
     * [2] Get OrderManager and submit order form
     * [3] Return order_no if successful
     *
     * @param type $user
     * @param ActiveCart $cart
     * @return type $result
     * @throws CException
     */
    public function confirm($user,ActiveCart $cart)
    {
        if ($cart->onPreview){
            throw new CException(Sii::t('sii','Action is disabled when shop is in preview'));
        }
                    
        $checkoutShop = $cart->getCheckoutShop();
        if (!isset($checkoutShop))
            throw new CException(Sii::t('sii','Checkout shop not found'));

        //identify user: either login user or guest (use internal id ACCOUNT::GUEST)
        $user = $this->parseOrderBuyer($user);
        
        //need to set db transaction as there are two level of db operation
        //one is CartManager, the other is OrderManager
        //Any point fails rollback all
        $transaction = Yii::app()->db->beginTransaction();
        
        try {
            //construct order form
            $form = $this->_constructOrderForm($user,$cart,$checkoutShop);

            //submit order form
            $order = $this->getOrderManager()->submit($user,$form);

            //start workflow transition
            $this->_runWorkflowBatch(
                    $user,
                    $cart->getCheckoutItems($checkoutShop),
                    __FUNCTION__,
                    'Confirmed by '.$user);

            //Track cart visit
            $this->getAnalyticManager()->trackPurchasedVisit($cart->getShop($checkoutShop)->account_id, $checkoutShop, Helper::getVisitor());

            //remove checkout items
            $cart->removeCheckoutItems($checkoutShop,$this->keepRecord);

            $transaction->commit();
            
            logInfo(__METHOD__.' ok');
        
            return $order->order_no;            

        } catch (CException $e) {
            logError(__METHOD__.' rollback: '.$e->getMessage().' >> '.$e->getTraceAsString(),[],false);
            $transaction->rollback();
            throw new ServiceOperationException($e->getMessage());
        }        
    }     
    /**
     * Insert cart item into DB, and perform attributes validation checks
     * 
     * @param type $item
     * @return \Cart
     * @throws CException
     */
    private function _insertItem($item)
    {
        $model = new Cart();
        $model->shopper = Helper::getVisitor();
        $model->attributes = $item->getAttributes([
                                'shop_id','product_id','shipping_id','itemkey',
                                'name','unit_price','quantity','weight',
                                'option_fee','shipping_surcharge','currency','weight_unit',
                            ]);
        if ($item->hasCampaign())
            $model->campaign = json_encode($item->getCampaignData());
        $model->total_price = $item->getTotalPrice();
        $model->total_weight = $item->getTotalWeight();
        $model->status = Cart::beginProcess();
        if (count($item->getOptions())>0)
            $model->options = json_encode($item->getOptions());
        if (!$model->validate()){
            logError('Fail to validate cart item', $model->getErrors(), false);
            throw new CException(Sii::t('sii','Fail to validate cart item'));
        }
        $model->insert();
        logTrace(__METHOD__.' ok for '.$model->name);
        return $model;
    }    
    /**
     * Update cart item fields (totals, options) in DB, and perform attributes validation checks
     * 
     * @param Cart $model
     * @param CartItemForm $item
     * @return \Cart
     * @throws CException
     */
    private function _updateItem($model,$item)
    {
        $model->attributes = $item->getAttributes([
                                'shop_id','product_id','shipping_id',
                                'name','unit_price','quantity','weight',
                                'option_fee','shipping_surcharge','currency','weight_unit'
                            ]);
        $model->total_price = $item->getTotalPrice();
        $model->total_weight = $item->getTotalWeight();
        if (!empty($item->getOptions()))
            $model->options = json_encode($item->getOptions());
        if (!$model->validate()){
            logError('Fail to validate cart item', $model->getErrors(), false);
            throw new CException(Sii::t('sii','Fail to validate cart item'));
        }
        $model->update();
        logTrace(__METHOD__.' ok for '.$model->name);
        return $model;
    }    
    /**
     * load item from DB Cart
     */
    private function _loadItem($item,$shopper)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['itemkey'=>$item->getKey()]);
        if (is_scalar($shopper))
            $criteria->addColumnCondition(['shopper'=>$shopper]);
        if (is_array($shopper))
            $criteria->addInCondition('shopper',$shopper);
        //logTrace(__METHOD__.' criteria',$criteria);
        return Cart::model()->incomplete()->find($criteria); 
    }    
    private function _runWorkflowBatch($user,$items,$action,$condition,$updateItem=false)
    {
        $models = $this->_constructItemModels($user,$items,$action,$updateItem);
        $this->validateModels($user, $models, false);
        $this->execute(Cart::model(), [
                self::WORKFLOW_BATCH=>[
                    'models'=>$models,
                    'transitionBy'=>$user,
                    'condition'=>$condition,
                    'action'=>ucfirst($action),
                    'decision'=>WorkflowManager::DECISION_NULL,
                    'saveTransition'=>$this->saveTransition,
                ]
            ],
            self::NO_VALIDATION
        );
    }
    /**
     * Construct item models array with validation and attribute update (optional)
     */
    private function _constructItemModels($user,$items,$action,$update=false)
    {
        $user = $this->parseCheckoutUser($user);
        $models = new CList();
        //validate cart items
        foreach ($items as $item){
            $model = $this->_loadItem($item,$user);
            if ($update){
                //update items with latest totals and options
                $this->_updateItem($model, $item);
            }
            //always set model status following current action to support Previous step feature (cart walking)
            $model->status = WorkflowManager::getProcessBeforeAction(Cart::model()->tableName(), $action);
            //form models array
            $models->add($model);
        }
        return $models->toArray();
    } 
    /**
     * Construct item models array with validation and attribute update (optional)
     */
    private function _constructOrderForm($user,$cart,$shop)
    {
        $form = new OrderForm();
        $form->account_id = $user;
        $form->shop_id = $shop;
        $form->currency = $cart->getShopCurrency($shop);
        $form->weight_unit = $cart->getShopWeightUnit($shop);
        $form->item_count = $cart->getCheckoutCount($shop);
        $form->item_shipping = json_encode($cart->getCheckoutShippingsByShop($shop,true)->toArray());
        $total = $cart->getCheckoutTotal($shop,Helper::NO_FORMAT,true,false);
        $form->item_total = $total->price;
        $form->item_weight = $total->weight;
        $form->shipping_total = $total->shippingRate;//excluding shipping surcharge
        $form->discount = $total->discountData;
        $form->tax = json_encode($cart->getTaxes($shop));//store tax breakdown
        $form->grand_total = $total->grandTotal;
        //data coming from CartPaymentForm
        $cartPayment = $cart->getPaymentMethod();
        $form->payment_method = $cartPayment->getPaymentMethodData();
        //set extra payment data, if any
        if (isset($cartPayment->extraPaymentData))
            $form->extraPaymentData = $cartPayment->extraPaymentData;
        //add checkout items
        foreach ($cart->getCheckoutItems($shop)->toArray() as $itemForm){
            $itemForm->payment_method = $form->payment_method;
            $form->addItem($itemForm);
        }
        //set shipping address excluding Pickup only
        if (!$cart->hasShippingMethodPickupOnly()){
            $form->setShippingAddress($cart->getShippingAddress());
            $form->remarks = $cart->getShippingAddress()->note;
        }
        logTrace(__METHOD__.' form attributes: ',$form->getAttributes());
        return $form;
    }      
    /**
     * Construct campaign item
     */
    private function _constructCampaignItem($item)
    {
        $campaignItem = new CartItemForm('campaign');
        $campaignItem->product_id = $item->getCampaignModel()->y_product->id;
        $campaignItem->name = $item->getCampaignModel()->y_product->name;
        $campaignItem->unit_price = $item->getCampaignModel()->y_product->unit_price;
        $campaignItem->quantity = $item->getCampaignModel()->scaleQuantityYByX($item->quantity);
        $campaignItem->weight = $item->getCampaignModel()->y_product->weight;
        $campaignItem->shop_id = $item->shop_id;
        $campaignItem->payment_method = $item->payment_method;
        $campaignItem->campaign_id = $item->campaign_id;
        $campaignItem->currency = $item->currency;
        $campaignItem->weight_unit = $item->weight_unit;
        //[1] follow item shipping id and shipping surcharge which already setup in assignShipping()
        $campaignItem->shipping_id = $item->shipping_id;
        $campaignItem->shipping_surcharge = $item->shipping_surcharge;
        
        //[2] Setup itemkey
        $campaignItemSku = Inventory::formatSKU($item->getCampaignModel()->y_product->code, $item->campaign_item['options']);
        if (!Yii::app()->serviceManager->getInventoryManager()->existsInventory($campaignItem->product_id,$campaignItemSku))
            throw new CException(Sii::t('sii','Cart item sku {sku} not found',['{sku}'=>$campaignItemSku]));
        $campaignItem->itemkey = CartData::formatItemKey(
                                    $campaignItemSku,
                                    $campaignItem->shop_id, 
                                    $campaignItem->shipping_id, 
                                    $campaignItem->campaign_id,
                                    $campaignItem->product_id);//this field act as seed to separate from $item, e.g. when x and y are equal, i.e. X_X_OFFER
        //[3] setup options
        if (isset($item->campaign_item['options'])){
            $campaignItem->assignOptions($item->campaign_item['options']);
        }
        //[4] configure campaign item
        $item->setCampaignItem(false);
        $campaignItem->setCampaignItem(true);
        $campaignItem->setAffinityKey($item->getKey());
        $campaignItem->setCheckout(true);
        logTrace(__METHOD__.' campaign item data',$campaignItem->attributes);
        return $campaignItem;
    } 
    /**
    * @return PayPalExpressCheckout
    */
    public function getPayPalExpress()
    {
        return Yii::app()->getModule('payments')->getPayPalExpress();
    }        
    /**
     * Parse user; For guest checkout, IP address will be returned
     * This is used for CART checkout steps before last step 'confirm'
     * @param type $user
     * @return type
     */
    protected function parseCheckoutUser($user)
    {
        //assign user to guest if true
        if (!isset($user)||$user==Account::GUEST){
            $guest = Helper::getGuest();
            logTrace(__METHOD__.' guest checkout',$guest);
            return $guest;
        }
        else 
            return $user;
    }
    /**
     * Parse buyer; For guest checkout, ACCOUNT::GUEST address will be returned
     * This is used for CART last step 'confirm' and also subsequent internal order and items workflow processing.
     * A valid account_id (integer) must be presented for this, hence guest checkout IP addres cannot be used.
     * @param type $user
     * @return type
     */
    protected function parseOrderBuyer($user)
    {
        if (Account::isSubType($user)){
            return $user;
        }
        elseif (!Helper::isInteger($user)){//inside is IP address
            //for guest checkout, assign general guest user id
            return Account::GUEST;
        }
        else {
            return $user;
        }
    }
}