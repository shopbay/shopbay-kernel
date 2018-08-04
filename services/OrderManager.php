<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.modules.orders.components.OrderData");
Yii::import("common.modules.orders.components.OrderNumberGenerator");
Yii::import("common.modules.campaigns.components.DiscountData");
Yii::import("common.modules.campaigns.components.CampaignSaleDiscountData");
Yii::import("common.modules.campaigns.components.CampaignPromocodeDiscountData");
/**
 * Description of Order Manager
 *
 * @author kwlok
 */
class OrderManager extends ServiceManager 
{
    const DTO_ITEMS = 'items';
    const DTO_SHIPPING_ADDRESS = 'shippingAddress';
    
    public function init() 
    {
        parent::init();
    }    
    /**
     * Submit (placing) an order
     * - Successful order are stored into s_order, s_item
     * 
     * Sequence of events: 
     * [1] Create order record
     * [2] Check inventory
     * [3] If has stock, proceed make payment
     * [4] If payment approved, proceed order transition (logic refer to OrderWorkflowBehavior)
     * [5] Process order workflow
     * [6] Create or update customer record if required
     * [7] Return order no
     * 
     * @param string $user
     * @param OrderForm $orderForm
     * @return CModel $order
     * @throws Exception
     */
    public function submit($user,$orderForm)
    {
        if (!($orderForm instanceof OrderForm))
            throw new CException(Sii::t('sii','Invalid order form'));
        
        //[1] Construct Order object 
        $order = new Order('Cart');
        //assign all attributes from OrderForm
        foreach ($orderForm->getAssignableAttributeNames() as $field)
            $order->{$field} = $orderForm->{$field};
        //system assigned attributes
        $order->order_no = (new OrderNumberGenerator($order))->generate();
        $order->status = WorkflowManager::beginProcess($order->tableName());
    
        if(!$order->validate()){
            logError('Validation failed',$order->getErrors());
            throw new CException(Sii::t('sii','Validation Error'));
        }

        //check inventory
        $itemsDTO = $this->_constructItemDTO($orderForm);
        foreach ($itemsDTO as $item) {
            $this->getInventoryManager()->checkInventory($item->product_id,$item->product_sku, $item->quantity);
        }
        
        //get payment decision
        $orderForm->order_no = $order->order_no;
        if ($orderForm->getPaymentMethodMode()==PaymentMethod::CASH_ON_DELIVERY)//deferred payment
            $paymentDecision = WorkflowManager::DECISION_DEFER;
        else if ($this->_makePayment($orderForm)==PaymentGateway::PAID)
            $paymentDecision = WorkflowManager::DECISION_ORDER;
        else 
            $paymentDecision = WorkflowManager::DECISION_HOLD;

        $condition = [
                        Transition::MESSAGE=>'System-assigned Order No '.$order->order_no,
                        Transition::PAYLOAD=>[
                            self::DTO_ITEMS=>$itemsDTO,
                            self::DTO_SHIPPING_ADDRESS=>$this->_constructShippingAddressDTO($orderForm),
                        ]
                    ];

        logTrace(__METHOD__.' start executing order workflow...');

        return $this->execute($order, [
                    'insert'=>self::EMPTY_PARAMS,
                    self::WORKFLOW=>[
                        'transitionBy'=>$user,
                        'condition'=>$condition,
                        'action'=>WorkflowManager::ACTION_PURCHASE,
                        'decision'=>$paymentDecision,
                        'saveTransition'=>true,
                    ],
                    'recordActivity'=>[
                        'event'=>Activity::EVENT_PURCHASE,
                    ],
                    'saveCustomerRecord'=>self::EMPTY_PARAMS,
                ]);
            
    }    
    private function _makePayment($orderForm)
    {
        $status = $this->getPaymentGateway()->process($this->_constructPaymentForm($orderForm));
        logTrace(__METHOD__.' status['.$status.'] ok');
        return $status;
    } 
    /**
     * Construct payment form
     */
    private function _constructPaymentForm($orderForm)
    {
        $paymentForm = new PaymentForm();
        $paymentForm->id = $orderForm->getPaymentMethodId();
        $paymentForm->method = $orderForm->getPaymentMethodMode();
        $paymentForm->amount = $orderForm->grand_total;
        $paymentForm->currency = $orderForm->currency;
        $paymentForm->reference_no = $orderForm->order_no;
        $paymentForm->payer = $orderForm->account_id;
        $paymentForm->type = Payment::SALE;
        $paymentForm->status = Process::UNPAID;
        $paymentForm->shop_id = $orderForm->shop_id;
        if (!empty($orderForm->extraPaymentData)){
            $paymentForm->extraPaymentData = $orderForm->extraPaymentData;
        }
        logTrace(__METHOD__,$paymentForm->getAttributes());
        return $paymentForm;
    }
    /**
     * Construct item models array 
     */
    private function _constructItemDTO($orderForm)
    {
        $dtos = new CList();
        //set order items
        foreach ($orderForm->getItems() as $itemForm){
            $dto = new stdClass();
            $attributes = $itemForm->getAttributes([
                            'name','unit_price','weight','payment_method',
                            'quantity','option_fee','shipping_surcharge',
                            'currency','weight_unit',
                        ]);
            foreach ($attributes as $key => $value) {
                $dto->{$key} = $value;
            }
            $dto->shop_id = $itemForm->getShop();
            $dto->shipping_id = $itemForm->getShipping();
            $dto->product_id = $itemForm->getProduct();
            $dto->product_sku = $itemForm->getProductSKU();
            $dto->product_url = $itemForm->getProductUrl();
            $dto->product_image = $itemForm->getProductImageUrl();
            $dto->total_price = $itemForm->getTotalPrice();
            if (!empty($itemForm->getOptions()))
                 $dto->options = json_encode($itemForm->getOptions());
            if ($itemForm->hasCampaign())
                $dto->campaign = json_encode($itemForm->getCampaignData());
            
            $dtos->add($dto);
        }
        
        return $dtos->toArray();
        
    }  
    /**
     * Construct item models array 
     */
    private function _constructShippingAddressDTO($orderForm)
    {
        if ($orderForm->getShippingAddress()!=null){
            $dto = new stdClass();
            foreach ($orderForm->getShippingAddress()->getAttributes() as $key => $value) {
                if ($key!='note')//this field is stored at $order->remarks
                    $dto->{$key} = $value;
            }
            return (array)$dto;
        }   
        return null;
    }    
    /**
     * Pay an order
     * 
     * @param integer $user Session user id
     * @param CModel $model Order model to pay
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function pay($user,$model,$transition)
    {
        return $this->runWorkflow(
                    $user,
                    $model, 
                    $transition, 
                    WorkflowManager::ACTION_PAY, //scenario
                    $transition->decision==WorkflowManager::DECISION_CANCEL?Activity::EVENT_CANCEL:Activity::EVENT_PAY, 
                    'payable');
    }  
    /**
     * Repay an order
     * 
     * @param integer $user Session user id
     * @param CModel $model Order model to pay
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function repay($user,$model,$transition)
    {
        return $this->runWorkflow(
                    $user,
                    $model, 
                    $transition, 
                    WorkflowManager::ACTION_PAY, //scenario
                    $transition->decision==WorkflowManager::DECISION_CANCEL?Activity::EVENT_CANCEL:Activity::EVENT_PAY, 
                    'repayable');
    }      
    /**
     * Verify payment of an order
     * 
     * @param integer $user Session user id
     * @param CModel $model Order model to verify
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function verify($user,$model,$transition)
    {
        $model->setAccountOwner('shop');
        return $this->runWorkflow(
                    $user,
                    $model, 
                    $transition, 
                    Transition::SCENARIO_C1_D, 
                    $transition->decision==WorkflowManager::DECISION_ACCEPT?Activity::EVENT_VERIFY:Activity::EVENT_REJECT, 
                    'verifiable');
    }    
    /**
     * Process an order
     * 
     * @param integer $user Session user id
     * @param CModel $model Order model to pay
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function process($user,$model,$transition)
    {
        $model->setScenario($transition->decision);
        if (!$model->validate()) {
            logError(__METHOD__.' validation error',$model->getErrors());
            throw new CException(Helper::htmlErrors($model->getErrors()));
        }

        return $this->runWorkflow(
                    $user,
                    $model, 
                    $transition, 
                    $model->orderDeferred()?WorkflowManager::ACTION_PAY:Transition::SCENARIO_C1_D, //scenario
                    $transition->decision==WorkflowManager::DECISION_FULFILL?Activity::EVENT_FULFILL:Activity::EVENT_CANCEL, 
                    'processable');
    }    
    /**
     * Refund an order
     * 
     * @param integer $user Session user id
     * @param CModel $model Order model to pay
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function refund($user,$model,$transition)
    {
        return $this->runWorkflow(
                    $user,
                    $model, 
                    $transition, 
                    WorkflowManager::ACTION_PAY, //scenario
                    Activity::EVENT_REFUND, 
                    'orderCancelled');
    }   
    /**
     * Calculate total after discount after tax
     * 
     * @param Order or Shop $model
     * @param decimal $initialPrice
     * @param decimal $initialShippingFee
     * @param integer $itemCount
     * @param string $promocode
     * @return array
     * @throws CException
     */
    public function calculatePriceAfterDiscountAfterTax($model,$initialPrice,$initialShippingFee,$itemCount=null,$promocode=null)
    {
        if (!($model instanceof Order || $model instanceof Shop)){
            throw new CException(Sii::t('sii','Invalid model'));
        }
        
        $hasSale = false;
        $hasPromo = false;
        $freeShipping = ['free'=>false,'by_sale'=>false,'by_promo'=>false];
        switch (get_class($model)) {
            case 'Shop':
                //[1] First process sale campaign
                if ($model->hasCampaign()){
                    $saleDiscountData = $this->getCampaignManager()->checkShopSalePrice($model->getCampaign(), $initialPrice, $itemCount, Helper::NO_FORMAT);
                    $freeShipping['by_sale'] = $saleDiscountData['free_shipping'];
                    $hasSale = $saleDiscountData['has_offer'];
                    $saleCampaignData = $saleDiscountData['campaign'];
                    $saleOfferType = $saleDiscountData['campaign']['offer_type'];
                    $saleOfferValue = $saleDiscountData['campaign']['offer_value'];
                }
                //[2] Second, process promocode campaign($shop,$amount,$code,$format=true)
                if (!empty($promocode)){
                    $promoDiscountData = $this->getCampaignManager()->checkShopPromocodePrice($model, $initialPrice, $promocode, Helper::NO_FORMAT);
                    $freeShipping['by_promo'] = $promoDiscountData['free_shipping'];
                    $hasPromo = $promoDiscountData['has_offer'];
                    $promoCampaignData = $promoDiscountData['campaign'];
                    $promoOfferType = $promoDiscountData['campaign']['offer_type'];
                    $promoOfferValue = $promoDiscountData['campaign']['offer_value'];
                }
                //[3] Setup other params
                $shop = $model;
                $freeShipping['free'] = $freeShipping['by_sale']||$freeShipping['by_promo'];
                break;
            case 'Order':
                $hasSale = $model->hasCampaignSale();//for order, this implies that sale campaign rules are met (either min purchase amt or qty)
                $saleOfferType = $model->getCampaignSaleOfferType();
                $saleOfferValue = $model->getCampaignSaleOfferValue();
                $saleCampaignData = (array)$model->getCampaignSaleData();
                $freeShipping = $model->getDiscountShippingData();
                logTrace(__METHOD__.' order mode',$freeShipping);
                $hasPromo = $model->hasCampaignPromocode();
                $promoOfferType = $model->getCampaignPromocodeOfferType();
                $promoOfferValue = $model->getCampaignPromocodeOfferValue();
                $promoCampaignData = (array)$model->getCampaignPromocodeData();
                if (!empty($promocode))
                    $promocode = $model->getCampaignPromocodeCode();
                $shop = $model->shop;
                break;
            default:
                throw new CException(Sii::t('sii','Invalid model.'));        
        }
        
        $discountData = $this->_constructDiscountData($model, $initialPrice, (object)[
            'hasSale'=>$hasSale,
            'saleOfferType'=>$hasSale?$saleOfferType:null,
            'saleOfferValue'=>$hasSale?$saleOfferValue:null,
            'saleCampaignData'=>($hasSale||$freeShipping['by_sale'])?$saleCampaignData:null,
            'hasPromo'=>$hasPromo,
            'promoOfferType'=>$hasPromo?$promoOfferType:null,
            'promoOfferValue'=>$hasPromo?$promoOfferValue:null,
            'promoCampaignData'=>($hasPromo||$freeShipping['by_promo'])?$promoCampaignData:null,
            'promocode'=>$hasPromo?$promocode:null,
            'freeShipping'=>$freeShipping,
            'shippingFee'=>$initialShippingFee,
        ]);
        $orderData = new OrderData($initialPrice,$initialShippingFee,$discountData);
        $orderData->hasSale = $hasSale;
        $orderData->hasPromo = $hasPromo;
        //Calculate tax: by default tax does not include shippingRate, 
        //but if shop has tax setting to include shipping rate, it will be included for tax computation
        $orderData->taxPayables = $this->getTaxManager()->checkPayables($shop,$orderData->priceAfterDiscount);
        $orderData->taxRate = $this->getTaxManager()->getPayablesRate($orderData->taxPayables);
        $orderData->tax = $this->getTaxManager()->getPayablesTotal($orderData->taxPayables);
        logTrace(__METHOD__.' ok: $orderData', $orderData->toArray());
        return $orderData;
    }      
    /**
     * Return payment gateway
     * @return type
     */
    protected function getPaymentGateway()
    {
        if (Yii::app()->hasModule('payments'))
            return Yii::app()->getModule('payments')->getPaymentGateway();
        throw new CException(Sii::t('sii','PaymentGateway service not available'));
    }  
    
    private function _constructDiscountData($model,$initialPrice,$config)
    {
        $priceAfterDiscount = $initialPrice;
        $discountData = new DiscountData();
        if ($config->hasSale){
            logTrace(__METHOD__.' compute sale discount..');
            $discountData->has_sale = true;
            $priceAfterDiscount = $this->getCampaignManager()->calculateOfferPrice($config->saleOfferType,$config->saleOfferValue,$initialPrice);
            $discountData->discount = $priceAfterDiscount - $initialPrice;
            $discountText = $model->formatCurrency($discountData->discount);
            $discountData->touchTotal();
            $discountData->createSaleData($priceAfterDiscount,$discountText,$config->saleCampaignData);
        }
        if ($config->hasPromo){
            logTrace(__METHOD__." compute promocode $config->promocode discount..");
            $discountData->has_promo = true;
            $priceAfterDiscount = $this->getCampaignManager()->calculateOfferPrice($config->promoOfferType,$config->promoOfferValue,$initialPrice);
            $discountData->discount = $priceAfterDiscount - $initialPrice;
            $discountText = $model->formatCurrency($discountData->discount);
            $discountData->touchTotal();
            $discountData->createPromoData($priceAfterDiscount,$discountText,$config->promoCampaignData,$config->promocode);
        }
        if ($config->freeShipping['free']){
            $discountData->free_shipping = true;
            $discountText = $model->formatCurrency(-$config->shippingFee);
            if ($config->freeShipping['by_sale'])
                $discountData->createSaleData($priceAfterDiscount,$discountText,$config->saleCampaignData,$config->freeShipping['by_sale']);
            if ($config->freeShipping['by_promo'])
                $discountData->createPromoData($priceAfterDiscount,$discountText,$config->promoCampaignData,$config->promocode,$config->freeShipping['by_promo']);
        }
        
        return $discountData;
    }
}
