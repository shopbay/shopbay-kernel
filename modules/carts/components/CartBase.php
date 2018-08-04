<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.orders.components.OrderData");
Yii::import("common.modules.carts.components.*");
/**
 * CartBase class to house cart session objects.
 * Support cart with multi-shops - different shops in cart have different currency
 * 
 * @author kwlok
 */
class CartBase extends CApplicationComponent 
{   
    /**
     * Cart item session state variable
     */
    protected $stateVariable = '_session_cartbase';   
    /**
     * Cart items containers (CMap)
     */
    protected $_m;
    /**
     * Cart shops containers (CMap)
     */
    protected $_s;
    /**
     * Cart shippings containers (CMap)
     */
    protected $_p;
    /**
     * Cart taxes containers (CMap)
     */
    protected $_t;
    /**
     * Initializes the CartBase.
     */
    public function init() 
    {
        parent::init();
        $this->_m = new CMap();
        $this->_s = new CMap();
        $this->_p = new CMap();
        $this->_t = new CMap();
        //Restores the shopping cart from the session
        $data = SActiveSession::get($this->stateVariable);
        if ($data instanceof Traversable)
            foreach ($data as $key => $item){
                $item->attachBehavior('CartItemTotalBehavior', new CartItemTotalBehavior());
                $item->attachBehavior('CartItemOptionBehavior', new CartItemOptionBehavior());
                $this->_m->add($key, $item);
                $this->_addShop($item);
                $this->_addShipping($item);
            }
    }    
    /**
     * Add item to the shopping cart
     * If the item was previously added to the cart,
     * then information about it is updated, and count increases by $quantity
     * @param CartItemForm $item
     * @param int count of elements positions
     */
    public function put(CartItemForm $item, $quantity = 1) 
    {
        $key = $item->getKey();
        if ($this->_m->itemAt($key) instanceof CartItemForm) {
            $item = $this->_m->itemAt($key);
            $oldQuantity = $item->quantity;
            $quantity += $oldQuantity;
        }
        $this->update($item, $quantity);
    }
    /**
     * Updates the position in the shopping cart
     * If position was previously added, then it will be updated in shopping cart,
     * if position was not previously in the cart, it will be added there.
     * If count is less than 1, the position will be deleted.
     *
     * Shop and Shipping is auto-setup when an item is updated
     * Since item contains both shop and shipping information
     * 
     * @param CartItemForm $item
     * @param int $quantity
     */
    public function update(CartItemForm $item, $quantity) 
    {
        if (!($item instanceof CComponent))
            throw new CException(Sii::t('sii','Cart item must implement CComponent interface'));

        $key = $item->getKey();

        $item->quantity = $quantity;

        $item->attachBehavior('CartItemTotalBehavior', new CartItemTotalBehavior());
        
        if ($item->getQuantity() < 1)
            $this->_m->remove($key);
        else
            $this->_m->add($key, $item);

        $this->_addShop($item);
                
        $this->_addShipping($item);

        $this->saveState();
    }
    /**
     * Removes position from the shopping cart of key
     * @param mixed $key
     */
    public function remove($key) 
    {
        $this->_m->remove($key);
        $this->saveState();
    }
    /**
     * Saves the state of the object in the session.
     * @return void
     */
    protected function saveState() 
    {
        SActiveSession::set($this->stateVariable, $this->_m);
    }
    /**
     * Clear the state of the object in the session.
     * @return void
     */
    protected function clearState() 
    {
        SActiveSession::set($this->stateVariable, null);
    }
    /**
     * Check if item exists in cart
     * 
     * @param CartItemForm $item
     * @return mixed True return item quantity; null item not found
     */
    public function existsItem($key){
        foreach ($this->_m as $item) {
            if ($item->getKey() == $key)
                return $item->getQuantity();
        }
        return false;
    }
    /**
     * Clear items by shop
     */
    public function clearItems($shop) 
    {
        foreach ($this->_m as $item){
            if ($item->getShop()==$shop)
               $this->remove($item->getKey());
        }
    } 
    /**
     * Returns array all cart items
     * @return array
     */
    public function getItems() 
    {
        return $this->_m;
    }
    /**
     * Returns array all checkout cart items by all or by shop
     * 
     * @param type $shop
     * @return \CMap items
     */
    public function getCheckoutItems($shop=null) 
    {
        $items = new CMap;
        foreach ($this->_m as $item) {
            if ($shop==null && $item->getCheckout())
                $items->add($item->getKey(),$item);
            if ($shop!=null && $item->getShop()==$shop && $item->getCheckout())
                $items->add($item->getKey(),$item);
        }
        return $items;
    }
    /**
     * Returns array all checkout cart items by shop and shipping
     * @return array
     */
    public function getCheckoutItemsByShopShipping($shop,$shipping) 
    {
        $items = new CMap;
        foreach ($this->_m as $item) {
            if ($item->getCheckout())
                if ($item->getShop()==$shop && $item->getShipping()==$shipping){
                    $items->add($item->getKey(),$item);
                }
        }
        return $items;
    }
    
    /**
     * Returns array all checkout cart items
     * @return array
     */
    public function getItemsByShopSortByShipping($shop) 
    {
        $items = new CMap;
        foreach ($this->_p->getKeys() as $shipping) {
            foreach ($this->_m as $item) {
                if ($item->getShop()==$shop && $item->getShipping()==$shipping)
                   $items->add($item->getKey(),$item);
            }
        }
        return $items;
    }
    
    /**
     * @return int count
     */
    public function getCount($shop=null,$items=null) 
    {
        if (!isset($items))
            $items = $this->_m;
        $count = 0;
        if ($shop==null){
            foreach ($items as $item)
                $count = $count + $item->getQuantity();
        }
        else {
            foreach ($items as $item) {
                if ($item->getShop()==$shop)
                    $count = $count + $item->getQuantity();
            }
        }
        return $count;
    } 
    
    public function getCheckoutCount($shop=null) 
    {
        return $this->getCount($shop,$this->getCheckoutItems($shop));
    }
    
    public function getNonCheckoutCount($shop=null) 
    {
        return $this->getCount() - $this->getCheckoutCount($shop);
    }
    /**
     * Returns array all cart items by shop
     * @return array
     */
    public function getItemsByShop($shop) 
    {
        $items = new CMap;
        foreach ($this->_m as $item) {
            if ($item->getShop()==$shop)
               $items->add($item->getKey(),$item);
        }
        return $items;
    }
    /**
     * Returns array all cart items by shop and shipping
     * @return array
     */
    public function getItemsByShopShipping($shop,$shipping)
    {
        $items = new CMap;
        foreach ($this->_m as $item) {
            if ($item->getShop()==$shop && $item->getShipping()==$shipping)
               $items->add($item->getKey(),$item);
        }
        return $items;
    }
    /**
     * Returns array all distinct shops
     * @return array
     */
    public function getShops($returnModel=false) 
    {
        if ($returnModel)
            return $this->_s->toArray();
        else
            return $this->_s->getKeys();
    }
    /**
     * Returns shop model
     * @return CActiveRecord
     */
    public function getShop($id) 
    {
        $model = $this->_s->itemAt($id);
        if ($model===null)
            return $this->_loadShopModel($id);
        else
            return $model;
    }    
    /**
     * Returns array all distinct shippings
     * @return array
     */
    public function getShippings($returnModel=false) 
    {
        if ($returnModel)
            return $this->_p->toArray();
        else
            return $this->_p->getKeys();
    }
    /**
     * Returns shipping model
     * @return CActiveRecord
     */
    public function getShipping($id) 
    {
        return $this->_p->itemAt($id);
    }    
    /**
     * Returns array all distinct tax payable for a particular shop
     * Note: This method can only be called after getCheckoutTotal is called so that $this->_t is loaded with data
     * 
     * @return array
     */
    public function getTaxes($shop) 
    {
        return $this->_t->itemAt($shop);
    }    
    /**
     * Returns array all distinct shippings
     * @return array
     */
    public function getShippingsByShop($shop,$withSubtotal=false) 
    {
        $shippings = new CMap;
        foreach ($this->_m as $item) {
            if ($item->getShop()==$shop)
                if ($withSubtotal)
                   $shippings->add($item->getShipping(),$this->getSubTotalByShipping($item->getShipping()));
                else
                   $shippings->add($item->getShipping(),$item->getShop());        
        }
        return $shippings;
    }
    /**
     * Returns array all distinct shops that has checkout items
     * @return array
     */
    public function getCheckoutShops($withSubtotal=false) 
    {
        $shops = new CMap();
        foreach ($this->_m as $item) {
            if ($item->getCheckout()){
                if ($withSubtotal)
                   $shops->add($item->getShop(),$this->getCheckoutSubTotalByShop($item->getShop()));
                else
                   $shops->add($item->getShop(),$item->getCurrency());
            }
        }
        return $shops;
    }
    /**
     * Returns array all distinct shppings that has checkout items
     * @return array
     */
    public function getCheckoutShippings($withSubtotal=false) 
    {
        $shippings = new CMap();
        foreach ($this->_m as $item) 
            if ($item->getCheckout())
                if ($withSubtotal)
                   $shippings->add($item->getShipping(),$this->getCheckoutSubTotalByShipping($item->getShipping()));
                else
                   $shippings->add($item->getShipping(),$item->getShop());
        
        return $shippings;
    }    
    /**
     * Returns array all distinct shipping per shop that has checkout items
     * @return array
     */
    public function getCheckoutShippingsByShop($shop,$withSubtotal=false) 
    {
        $shippings = new CMap();
        foreach ($this->_m as $item) 
            if ($item->getCheckout() && $item->getShop()==$shop)
                if ($withSubtotal)
                   $shippings->add($item->getShipping(),$this->getCheckoutSubTotalByShipping($item->getShipping()));
                else
                   $shippings->add($item->getShipping(),$item->getShop());
        return $shippings;
    }    
    /**
     * Clear checkout items by shop
     */
    public function clearCheckoutItems($shop) 
    {
        foreach ($this->_m as $item){
            if ($item->getCheckout() && $item->getShop()==$shop)
               $this->remove($item->getKey());
        }
    } 
    /**
     * Checkout the cart item (Affinity item will get checkout also)
     * @return array
     */
    public function checkoutItem($key,$checkout=true) 
    {
        $item = $this->_m->itemAt($key);
        $item->setCheckout($checkout);
        logTrace('checkout item '.$key.' '.$checkout);
        if ($item->hasCampaignBgaG()){
            $affinityItem = $this->getAffinityItem($key);
            if ($affinityItem!=false){
                $affinityItem->setCheckout($checkout);
                logTrace('checkout affinity item '.$affinityItem->getKey().' '.$checkout);
            }
        } 
    }      
    /**
     * Checkout the cart items
     * @return array
     */
    public function checkoutItems($keys,$checkout=true) 
    {
        if (!is_array($keys))
            throw new CException(Sii::t('sii','Checkout keys must be array'));        
        //checkout targetted item
        logTrace('checkout keys = ',$keys);
        foreach ($keys as $key)
            $this->checkoutItem($key,$checkout);
    }      
    /**
     * Checkout the cart items
     * @return array
     */
    public function checkout($keys) 
    {
        if (!is_array($keys))
            throw new CException(Sii::t('sii','Checkout keys must be array'));
        //reset all item checkout statues
        foreach ($this->_m as $item)
            $item->setCheckout(false);
        //checkout targetted item
        logTrace('CartBase keys = ',$this->_m->getKeys());
        foreach ($keys as $key)
            $this->_m->itemAt($key)->setCheckout(true);
    }  
    /**
     * Checkout all items
     * @param type $bool
     */
    public function checkoutAll($bool) 
    {
        foreach ($this->_m as $item)
            $item->setCheckout($bool);
    } 
    /**
     * Get item with affinity key
     * @param type $key
     * @return boolean
     */
    public function getAffinityItem($key) 
    {
        foreach($this->_m as $item){
            if ($item->getAffinityKey()==$key){
                return $item;
            }
        }
        return false;
    } 
    /**
     * Retrieve item by key
     * @param type $key
     * @return type
     */
    public function itemAt($key) 
    {
        return $this->_m->itemAt($key);
    } 
    /**
     * Returns if cart is empty
     * @return bool
     */
    public function isEmpty($shop=null) 
    {
        return !(bool)$this->getCount($shop);
    }
    /**
     * Returns shipping rate or shipping information (verbose mode).
     * @see CartShippingData
     * @return mixed Array or float
     */
    public function getShippingRate($model,$priceTotal,$weightTotal,$verbose=false,$format=false) 
    {
        $shippingData = new CartShippingData($model, $priceTotal, $weightTotal);
        if ($verbose)
            return $shippingData->toArray($format);
        else                    
            return $format?$model->formatCurrency($shippingData->shippingRate):$shippingData->shippingRate;
    }    
    /**
     * Returns shipping subtotal information.
     * 
     * Note: Shipping level grand_total is not accurate when there is a shop-wide campaign (CampaignSale) and more than one shippings in one order.
     *       Because CampaginSale is based on min purchase/quantity in one order (some may from shipping #1, some from shippng #2 etc)
     * 
     * @see CartShippingData
     * @see CartCheckoutData::shippingSubtotalArray
     * @return mixed Array or float
     */
    private function _getSubTotalByShippingInternal($model,$shippingSubtotalData,$format=false,$returnArray=true)
    {
        if (!$shippingSubtotalData instanceof CartCheckoutData)
            throw new CException('Invalid data format');
        
        $shippingRate = 0;
        if ($shippingSubtotalData->price > 0){
            $shippingRate = $this->getShippingRate($model, $shippingSubtotalData->price, $shippingSubtotalData->weight);
            $shippingSubtotalData->increaseValue('shippingFee', $shippingRate);
        }
        $subtotal = $this->getShippingRate($model, $shippingSubtotalData->price, $shippingSubtotalData->weight, true, $format);
        if ($returnArray)
            return array_merge($subtotal,$shippingSubtotalData->shippingSubtotalArray($format?$model:null));
        else {
            $shippingSubtotalData->shippingRate = $subtotal['shipping_rate'];
            return $shippingSubtotalData;
        }
    }
    /**
     * Returns subtotal price for all checkout items of a shipping in the shopping cart.
     * @see self::_getSubTotalByShippingInternal() for return data elements
     * @return array
     */
    public function getCheckoutSubTotalByShipping($shipping,$format=false,$returnArray=true) 
    {
        if ($shipping instanceof Shipping)
            $id = $shipping->id;
        else {
            $id = $shipping;
            $shipping = $this->_loadShippingModel($id);
        }
            
        $price_subtotal = 0.0;
        $weight_subtotal = 0;
        $shipping_surcharge_subtotal = 0.0;
        foreach ($this->_m as $item) {
            if ($item->getCheckout() && $item->getShipping()==$id){
                $price_subtotal += $item->getTotalPrice();  
                $weight_subtotal += $item->getTotalWeight(); 
                $shipping_surcharge_subtotal += $item->getTotalShippingSurcharge();   
            }
        }
        $shippingSubtotalData = $this->getInitialShippingSubtotalData($price_subtotal, $weight_subtotal, $shipping_surcharge_subtotal);
        return $this->_getSubTotalByShippingInternal($shipping,$shippingSubtotalData,$format,$returnArray);
    } 
    /**
     * Returns shop subtotal information.
     * @see CartCheckoutData::shopSubtotalArray
     * @return mixed Array or float
     */
    public function getCheckoutSubTotalByShop($shop,$returnArray=true) 
    {
        $shopSubtotalData = new CartCheckoutData();
        foreach ($this->getCheckoutShippingsByShop($shop)->getKeys() as $shipping) {
            $shippingSubtotal = $this->getCheckoutSubTotalByShipping($shipping,false,false);
            $shopSubtotalData->increaseValue('price', $shippingSubtotal->price);
            $shopSubtotalData->increaseValue('weight', $shippingSubtotal->weight);
            $shopSubtotalData->increaseValue('shippingRate', $shippingSubtotal->shippingRate);
            $shopSubtotalData->increaseValue('shippingFee', $shippingSubtotal->shippingFee);
        }
        if ($returnArray)
            return $shopSubtotalData->shopSubtotalArray();
        else
            return $shopSubtotalData;
    } 
    /**
     * Returns total for checkout items across shop and shippings
     * 
     * @see OrderData
     * @see CartCheckoutData::toArray()
     * @return object
     */
    public function getCheckoutTotal($shop=null,$format=false,$includeDiscountTax=true,$returnArray=true) 
    {
        $checkoutData = new CartCheckoutData();
        if ($shop==null){
            foreach($this->getCheckoutShops()->getKeys() as $id ){
                $checkoutData->updateValues($this->getCheckoutTotal($id));
            }            
        }
        else {
            $subtotal = $this->getCheckoutSubTotalByShop($shop,false);
            $checkoutData->updateValues($subtotal->shopSubtotalArray());
            if ($includeDiscountTax){
                $orderData = $this->_computeTotalAfterDiscountAfterTax($shop, $checkoutData->price, $subtotal->shippingRate);
                $checkoutData->transferOrderData($orderData);
                $checkoutData->updateValues($orderData);  
            }
        }
        if ($returnArray){
            $model = isset($shop)?$this->getShop($shop):$this->getShop($this->getCheckoutShop());//use checkout shop for currency formatting
            logTraceDump(__METHOD__.' checkout data for shop '.$shop,$checkoutData->toArray($format?$model:null));
            return $checkoutData->toArray($format?$model:null);
        }
        else {
            return $checkoutData;
        }
    }
    /**
     * This method computes cart checkout grand total after discounts after taxes.
     * 
     * It checks:
     * [1] If there is CampaignSale (shop level discount based on certain rules, e.g. min purchase amt etc)
     * [2] If there is CampaignPromocode (shop level discount based on promotional code)
     * [3] Calculate taxes payable after discount (default shipping rate is not taken into consideration when calculating taxes)
     * 
     * @param type $shop
     * @param type $initialTotal
     * @param type $shippingRate Default this field is not used for calculating tax. Here is required as it is part of grand total
     * @return type
     */
    private function _computeTotalAfterDiscountAfterTax($shop,$initialTotal,$shippingRate)
    {
        if (!($shop instanceof Shop))
            $shop = $this->_loadShopModel($shop);
        $orderData = Yii::app()->serviceManager->getOrderManager()->calculatePriceAfterDiscountAfterTax($shop,$initialTotal,$shippingRate,$this->getCheckoutCount($shop->id),$this->getPromocode($shop->id));
        $this->_addTax($shop->id, $orderData->taxPayables);//add tax payable to session
        return $orderData;
    }
    /**
     * Return grand total component only
     * 
     * @param type $shop
     * @param type $format
     * @return mixed
     */
    public function getCheckoutGrandTotal($shop=null,$format=false)
    {
        $total = $this->getCheckoutTotal($shop, $format);
        return $total['grand_total'];
    }
    /**
     * Return item total component only
     * 
     * @param type $shop
     * @param type $format
     * @return mixed
     */
    public function getCheckoutItemTotal($shop=null,$format=false)
    {
        $total = $this->getCheckoutTotal($shop, $format,false);
        return $total['price'];
    }
    /**
     * Returns total price for all items of a shop in the shopping cart.
     * @return array
     */
    public function getSubTotalByShipping($shipping,$format=false) 
    {
        if ($shipping instanceof Shipping)
            $id = $shipping->id;
        else {
            $id = $shipping;
            $shipping = $this->_loadShippingModel($id);
        }
        
        $price_subtotal = 0.0;
        $weight_subtotal = 0;
        $shipping_surcharge_subtotal = 0.0;
        foreach ($this->_m as $item) {
            if ($item->getShipping()==$id){
                $price_subtotal += $item->getTotalPrice();  
                $weight_subtotal += $item->getTotalWeight();  
                $shipping_surcharge_subtotal += $item->getTotalShippingSurcharge();   
            }
        }
        $shippingSubtotalData = $this->getInitialShippingSubtotalData($price_subtotal, $weight_subtotal, $shipping_surcharge_subtotal);
        return $this->_getSubTotalByShippingInternal($shipping,$shippingSubtotalData,$format);
    }         
    /**
     * Wrapper of method getShippingRate($verbose=true)
     * @return array shipping fee
     */
    public function getShippingData($shipping) 
    {
        $model = $this->_loadShippingModel($shipping);
        return $this->getShippingRate($model, 
                                      $this->getTotalPriceByShipping($shipping), 
                                      $this->getTotalWeightByShipping($shipping),
                                      true);
    }
    /**
     * @return object Initial shipping subtotal object
     */
    public function getInitialShippingSubtotalData($price,$weight,$shippingSurcharge) 
    {
        $data = new CartCheckoutData();
        $data->price = $price;
        $data->weight = $weight;
        $data->shippingSurcharge = $shippingSurcharge;
        $data->shippingFee = $shippingSurcharge;//initial value
        return $data;
    }    
    /**
     * Returns total price by shipping
     * @param integer $shipping
     * @return float
     */
    public function getTotalPriceByShipping($shipping)
    {
        $total = 0.0;
        foreach ($this->_m as $item)
            if ($item->getShipping()==$shipping)
                $total += $item->getTotalPrice();
        return $total;
    }    
    /**
     * Returns total weight by shipping
     * @param integer $shipping
     * @return float
     */
    public function getTotalWeightByShipping($shipping) 
    {
        $total = 0.0;
        foreach ($this->_m as $item)
            if ($item->getShipping()==$shipping)
                $total += $item->getTotalWeight();
        return $total;
    }     
    /**
     * Returns checkout total weight
     * @return float
     */
    public function getCheckoutTotalWeight() 
    {
        $total = 0.0;
        foreach ($this->_m as $item)
            if ($item->getCheckout())
                    $total += $item->getWeight();   
        return $total;
    }
    /**
     * Returns shop currency. 
     * All cart items within a shop should have same currency 
     * @return string
     */
    public function getShopCurrency($shop) 
    {    
        $model = $this->getShop($shop);
        return $model!=null?$model->currency:null;
    }    
    /**
     * Returns shop wight unit. 
     * All cart items within a shop should have same weight unit 
     * @return string
     */
    public function getShopWeightUnit($shop) 
    {    
        $model = $this->getShop($shop);
        return $model!=null?$model->weight_unit:null;
    }   
    /**
     * @return mixed session checkout shop id
     */
    public function getCheckoutShop()
    {
        return unserialize(SActiveSession::get($this->stateVariable.'_checkoutshop',null));
    }
    /**
     * Support only one shop per checkout
     * 
     * @param type $shop
     */
    public function setCheckoutShop($shop)
    {
        SActiveSession::set($this->stateVariable.'_checkoutshop',serialize($shop));
    }
    /**
     * @return boolean Check if has session shipping address
     */
    public function hasShippingAddress()
    {
        return SActiveSession::exists($this->stateVariable.'_address');
    }
    /**
     * @return mixed session shipping address
     */
    public function getShippingAddress()
    {
        return unserialize(SActiveSession::get($this->stateVariable.'_address'));
    }
    /**
     * @param mixed session shipping address
     */
    public function setShippingAddress($address)
    {
        SActiveSession::set($this->stateVariable.'_address',serialize($address));
    }
    /**
     * @return mixed session payment method
     */
    public function getPaymentMethod()
    {
        return unserialize(SActiveSession::get($this->stateVariable.'_paymentmethod'));
    }
    /**
     * @param mixed session payment method
     */
    public function setPaymentMethod($method)
    {
        SActiveSession::set($this->stateVariable.'_paymentmethod',serialize($method));
    }
    /**
     * @return mixed session paypal express response
     */
    public function getPayPalExpressResponse()
    {
        return unserialize(SActiveSession::get($this->stateVariable.'_paypalexpress'));
    }
    /**
     * @param mixed session paypal express response
     */
    public function setPaypalExpressResponse($response)
    {
        SActiveSession::set($this->stateVariable.'_paypalexpress',serialize($response));
    }
    /**
     * @return mixed session shop promocode
     */
    public function getPromocode($shop)
    {
        return unserialize(SActiveSession::get($this->stateVariable.'_promocode_'.$shop,null));
    }
    /**
     * @param mixed session shop promocode
     */
    public function setPromocode($shop,$code)
    {
        SActiveSession::set($this->stateVariable.'_promocode_'.$shop,serialize($code));
    }
    /**
     * @return string session checkout token (no need to login)
     */
    public function getCheckoutToken($shop)
    {
        return SActiveSession::get($this->stateVariable.'_checkouttoken_'.$shop,null);
    }
    /**
     * @param string session checkout token
     */
    public function setCheckoutToken($shop,$token)
    {
        SActiveSession::set($this->stateVariable.'_checkouttoken_'.$shop,$token);
    }
    /**
     * Clear session variable; For example, when cart is checkout, this method is called.
     * Following is not cleared:
     * [1] Do not clear promocode as we need the data for checkout cart processing
     * [2] Do not clear shipping address for usabiblity reason; user do not have to key in again
     */
    public function clearSessionVariables($clearCampaignData=true)
    {
        //nullify cart session
        $this->setCheckoutShop(null);//set to null
        $this->setPaypalExpressResponse(null);//set to null
        $this->setPaymentMethod(null);//set to null
        if ($clearCampaignData){
            foreach ($this->getShops() as $shop) {
                $this->setPromocode($shop,null);//set to null
            }
        }
        //$this->setShippingAddress(null);//set to null; 
    }
    /**
     * Validate shipping address to check if the shipping zone is supported
     * It scan through all the shippings in the cart
     * @see Zone::validateShippingAddress
     * @param type $shop The shop to perform shipping zone validation
     * @param type $zones The target zones to check
     * @return array of errors
     */
    public function validateShippingZone($shop,$zones,$locale)
    {
        $errors = [];
        foreach ($this->getCheckoutShippingsByShop($shop) as $shipping_id => $shop_id) {
            logTrace(__METHOD__.' shipping id',$shipping_id);
            $errors = array_merge($errors,Zone::validateShippingAddress($this->getShippingAddress(), $this->_loadShippingModel($shipping_id), $zones, $locale));
        }
        return $errors;
    }
    /**
     * Check if there is only shipping method "pickup" in the cart
     * @return boolean
     */
    public function hasShippingMethodPickupOnly()
    {
        $count = 0;
        foreach ($this->getCheckoutShippings()->keys as $shippingId){
            $model = $this->_loadShippingModel($shippingId);
            if ($model->method==Shipping::METHOD_LOCAL_PICKUP)
                $count++;
        }
        return count($this->getCheckoutShippings())==$count;
    }
    /**
     * Add shop and load model into container
     * @param type $item
     */
    private function _addShop($item)
    {
        $this->_loadShopModel($item->getShop());
    }
    /**
     * Add shipping and load model into container
     * @param type $item
     */
    private function _addShipping($item)
    {
        $this->_loadShippingModel($item->getShipping());
    }
    /**
     * Add tax data into container
     * Tax Payables data structure:
     * array(
     *  'tax name'=>'tax payable',
     *  'tax name'=>'tax payable',
     * )
     * @see $this->_checkTaxes() for add tax
     * @param integer $shop Shop id
     * @param array $taxPayables 
     */
    private function _addTax($shop,$taxPayables)
    {
        $this->_t->add($shop,$taxPayables);
    }
    /**
     * @return Shop model
     */
    private function _loadShopModel($id) 
    {
        if (!$this->_s->contains($id)){
            $model = Shop::model()->findByPk($id);
            if ($model===null)
                throw new CException(Sii::t('sii','Shop not found'));
            //logTrace(__METHOD__,$model->getAttributes());
            $this->_s->add($id,$model);
        }
        return $this->_s->itemAt($id);        
    }    
    /**
     * @return Shipping model
     */
    private function _loadShippingModel($id) 
    {
        if (!$this->_p->contains($id)){
            $model = Shipping::model()->findByPk($id);
            if ($model===null)
                throw new CException(Sii::t('sii','Shipping not found'));
            //logTrace(__METHOD__,$model->getAttributes());
            $this->_p->add($id,$model);
        }
        return $this->_p->itemAt($id);        
    }    
}