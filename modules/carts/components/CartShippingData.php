<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CartShippingData; 
 * Returns shipping rate or shipping information (verbose mode).
 * 
 * Data elements (refer to s_shipping):
 * [1] shop_id 
 * [2] shipping_id 
 * [3] shipping_name 
 * [4] shipping_method 
 * [5] shipping_type 
 * [6] shipping_rate = shipping rate at order level
 * 
 * @author kwlok
 */
class CartShippingData extends CComponent 
{
    /*
     * Private property
     */
    private $_m;//shipping model
    private $_price;//total price
    private $_weight;//total item weight
    /**
     * Data constructor
     * @param type $shippingModel
     * @param type $rate
     * @throws CException
     */
    public function __construct($shippingModel,$price,$weight) 
    {
        if (!$shippingModel instanceof Shipping)
            throw new CException('Invalid Shipping');
        $this->_m = $shippingModel;
        $this->_price = $price;
        $this->_weight = $weight;
    }
    
    public function getShippingRate()
    {
        $modelTier = $this->_m->getTierBase();
        if ($modelTier!=null && $modelTier->base==ShippingTier::BASE_WEIGHT)
            return $this->_m->calculateRate($this->_weight);
        else
            return $this->_m->calculateRate($this->_price);    
    }
    
    public function toArray($format=false)
    {
        return array(
            'shop_id'=>$this->_m->shop->id,
            'shipping_id'=>$this->_m->id,
            'shipping_name'=>$this->_m->name,
            'shipping_method'=>array('value'=>$this->_m->method,'description'=>$this->_m->getMethodDesc()),
            'shipping_type'=>array('value'=>$this->_m->type,'description'=>$this->_m->getTypeDesc()),
            'shipping_speed'=>$this->_m->speed,
            'shipping_rate_text'=>$this->_m->getShippingRateText(),
            'shipping_rate'=>$format?$this->_m->formatCurrency($this->shippingRate):$this->shippingRate,
        );
    }
    
}
