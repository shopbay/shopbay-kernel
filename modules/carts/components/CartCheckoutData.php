<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.orders.components.OrderData");
/**
 * Description of CartCheckoutData:
 * Contains summary for checkout items across shop and shippings
 * 
 * @author kwlok
 */
class CartCheckoutData extends OrderData
{
    /*
     * Total price of checkout items (including product option fee and product shipping surcharge)
     */
    public $price = 0;
    /*
     * Total weight of checkout items
     */
    public $weight = 0;
    /*
     * Shipping surcharge at product level (if any)
     */
    public $shippingSurcharge = 0;
    /*
     * Shipping rate defined at order level
     */
    public $shippingRate = 0;
    /*
     * Total shipping fee = sum of $shippingRate and product level $shippingSurcharge of checkout items
     */
    public $shippingFee = 0;
    
    public function toArray($formatterModel=null)
    {
        $array = parent::toArray();
        if (isset($formatterModel)){
            $array['discount'] = $formatterModel->formatCurrency($array['discount']);
            $array['tax'] = $formatterModel->formatCurrency($array['tax']);
            $array['grand_total'] = $formatterModel->formatCurrency($array['grand_total']);
        }
        return array_merge($this->totalArray($formatterModel),$array);
    }    
    
    public function totalArray($formatterModel=null)
    {
        return array(
            'price' => isset($formatterModel)?$formatterModel->formatCurrency($this->price):$this->price,
            'weight' => isset($formatterModel)?$formatterModel->formatWeight($this->weight):$this->weight,
            'shipping_surcharge' => isset($formatterModel)?$formatterModel->formatCurrency($this->shippingSurcharge):$this->shippingSurcharge,
            'shipping_rate' => isset($formatterModel)?$formatterModel->formatCurrency($this->shippingRate):$this->shippingRate,
            'shipping_fee' => isset($formatterModel)?$formatterModel->formatCurrency($this->shippingFee):$this->shippingFee,
        );
    }    
    
    public function shopSubtotalArray($formatterModel=null)
    {
        $totalArray = $this->totalArray($formatterModel);
        unset($totalArray['shipping_surcharge']);
        return $totalArray;
    }    

    public function shippingSubtotalArray($formatterModel=null)
    {
        $totalArray = $this->totalArray($formatterModel);
        unset($totalArray['shipping_rate']);
        return $totalArray;
    }    
    /**
     * Increase existing attribute value
     * @param type $attribute
     * @param type $value
     */
    public function increaseValue($attribute,$value)
    {
        $this->$attribute += $value;
    }
    /**
     * Batch update values (depends on if is_numeric)
     * @param mixed(array or object) $attributes
     */
    public function updateValues($attributes,$excludes=array())
    {
        foreach ($attributes as $key => $value) {
            if (!in_array($key, $excludes)){
                if (is_numeric($value)){
                    //logTraceDump(__METHOD__.' numeric $key='.$key.' , before value: '.$this->{Helper::camelCase($key)});
                    if (property_exists($this,Helper::camelCase($key)))
                        $this->{Helper::camelCase($key)} += $value;
                }
                else {
                    //logTraceDump(__METHOD__.' non-numeric $key='.$key.' , before value: '.$this->{Helper::camelCase($key)});
                    if (property_exists($this,Helper::camelCase($key)))
                        $this->{Helper::camelCase($key)} = $value;
                }
            }
        }
    }    
    /**
     * This init cart checkout data based on calculated order data
     * @param type $orderData
     */
    public function transferOrderData($orderData)
    {
        $this->setPriceBeforeDiscount($orderData->priceBeforeDiscount);
        $this->setDiscountData($orderData->rawDiscountData);
        $this->setShippingFeeBeforeDiscount($orderData->shippingFeeBeforeDiscount);
    }
}
