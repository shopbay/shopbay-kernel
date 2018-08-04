<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShippableBehavior
 *
 * Data elements:
 * [1] shop_id 
 * [2] shipping_id 
 * [3] shipping_name 
 * [4] shipping_method 
 * [5] shipping_type 
 * [6] shipping_rate = shipping rate defined at order level
 * [7] shipping_surcharge = shipping surcharge at product level (if any)
 * [8] shipping_fee = sum of [6] shipping_rate and [7] shipping_surcharge (product level) if any
 * [9] price = subtotal price of checkout items
 * [10] weight = subtotal weight of checkout items
 * 
 * @see CartBase::getCheckoutTotal()
 * 
 * @author kwlok
 */
class ShippableBehavior extends CActiveRecordBehavior 
{
    /**
     * @var string The name of shipping data attribute that stores json encoded shipping data. Defaults to "item_shipping"
     */
    public $shippingDataAttribute = 'item_shipping';
    /**
     * @var string The name of shipping id attribute that stores shippig id. Defaults to "null"
     */
    public $shippingIdAttribute;
    /**
     * json encoded data example:
     * {"3"://shipping id
     *   {"shop_id":"3",
     *    "shipping_id":"3",
     *    "shipping_name":"Registered Mail",
     *    "shipping_method":{
     *       "value":"1",
     *       "description":"Home Delivery"
     *    },
     *    "shipping_type":{
     *       "value":"1",
     *       "description":"Flat Fee"
     *    },
     *    "shipping_speed":"2",
     *    "shipping_rate_text":"$1.00", // or in array form: "shipping_rate_text":["From $0.00 to $999.00: $1.00"]
     *    "shipping_rate":"1.00",
     *    "price":1,
     *    "weight":1,
     *    "shipping_surcharge":0,
     *    "shipping_fee":1
     *   }
     * }
     * 
     * @return associative array
     */
    private $_d;
    public function getShippingData($id=null) 
    {
        if (isset($id)){
            return array($id=>$this->getShippingValues($id));
        }
        
        if ($this->_d==null)
            $this->_d = json_decode($this->getOwner()->{$this->shippingDataAttribute},true);//return associative array
        return $this->_d;
    }
    /**
     * Check if has shipping
     * @return array
     */
    public function hasShipping() 
    {
        return $this->getShippingData()!=null;
    }
    /**
     * Return shipping ids in array
     * @return array
     */
    public function getShippings() 
    {
        return $this->hasShipping()?array_keys($this->getShippingData()):array();
    }
    /**
     * Return shipping all values in $shippingData
     * @return array
     */
    public function getShippingValues($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]:array();
    }
    /**
     * Return shipping name
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingName($locale=null,$id=null) 
    {
        if ($this->hasShipping()){
            if (!isset($locale))
                $locale = $this->getOwner()->getLocale();
            $name = $this->_d[$this->_getShippingId($id)]['shipping_name'];
            return $this->getOwner()->parseLanguageValue($name,$locale);
        }
        else
            return null;
    }
    /**
     * Return item subtotal price (at order shipping level)
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingItemSubtotal($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['price']:null;
    }
    /**
     * Return item subtotal weight (at order shipping level)
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingItemWeight($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['weight']:null;
    }
    /**
     * Return shipping rate (at order shipping level)
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingRate($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['shipping_rate']:null;
    }
    /**
     * Return shipping rate text (at order shipping level)
     * @param integer $id Shipping id
     * @return mixed String or array
     */
    public function getShippingRateText($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['shipping_rate_text']:null;
    }
    /**
     * Return shipping surcharge (aggregate of all item of order shipping level)
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingSurcharge($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['shipping_surcharge']:null;
    }
    /**
     * Return shipping total (sum of shipping rate + shipping surcharge) at order shipping level
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingTotal($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['shipping_fee']:null;
    }
    /**
     * Format shipping total (sum of shipping rate + shipping surcharge)
     * @param integer $id Shipping id
     * @return string
     */
    public function formatShippingTotal($shippingTotal,$locale,$showLink=false)
    {
        $text = $this->getOwner()->formatCurrency($shippingTotal);
        if ($this->getOwner()->hasDiscountFreeShipping()){
            $text = CHtml::tag('span', array('class'=>'shipping-total-original','style'=>'text-decoration: line-through;'),$text);
            $text .= $this->getOwner()->getDiscountFreeShippingColorTag($locale,$showLink);
        }
        return $text;
    }    
    /**
     * Return shipping method 
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingMethod($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['shipping_method']['value']:null;
    }
    /**
     * Return shipping method description
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingMethodDesc($id=null) 
    {
        return $this->hasShipping()?Sii::t('sii',$this->_d[$this->_getShippingId($id)]['shipping_method']['description']):null;
    }
    /**
     * Return shipping type 
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingType($id=null) 
    {
        if (!isset($id)){
            return $this->_getShippingId();
        }
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['shipping_type']['value']:null;
    }
    /**
     * Return shipping type description
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingTypeDesc($id=null) 
    {
        return $this->hasShipping()?Sii::t('sii',$this->_d[$this->_getShippingId($id)]['shipping_type']['description']):null;
    }
    /**
     * Return shipping speed (estimated time arrival - ETA) 
     * @param integer $id Shipping id
     * @return string
     */
    public function getShippingSpeed($id=null) 
    {
        return $this->hasShipping()?$this->_d[$this->_getShippingId($id)]['shipping_speed']:null;
    }
    /**
     * Check if shipping method is Pickup only
     * @return type
     */
    public function hasShippingMethodPickupOnly()
    {
        $count = 0;
        foreach ($this->getShippings() as $shipping){
            if ($this->getShippingMethod($shipping)==Shipping::METHOD_LOCAL_PICKUP)
                $count++;
        }
        return count($this->getShippings())==$count;
    }
    /**
     * Return value of $shippingIdAttribute if set
     * @return integer
     * @throws CException
     */ 
    private function _getShippingId($id=null) 
    {
        if (isset($id)){
            return $id;
        }
        else {
            if (!isset($this->getOwner()->{$this->shippingIdAttribute}))
                throw new CException(Sii::t('sii','Shippable shipping id attribute not set'));
            return $this->getOwner()->{$this->shippingIdAttribute};
        }
    }
}
