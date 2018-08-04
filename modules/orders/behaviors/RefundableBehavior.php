<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of RefundableBehavior
 *
 * Data elements:
 * [1] obj_type 
 * [2] obj_id 
 * [3] items //the expected refunded items (for those in CANCELLED status)
 * [4] shipping_orders //the shipping orders that have refund
 * [5] include_shipping_rate //true/false; whether to include shipping rate for refund; Default to N
 * [6] actual_amount //the merchat actual refund amount, in normal case this should equal to "total"
 * [7] supporting_info //the supporting info
 * 
 * @author kwlok
 */
class RefundableBehavior extends CActiveRecordBehavior 
{
    /**
     * @var string The name of refund data attribute that stores json encoded refund data. Defaults to "refund"
     */
    public $refundDataAttribute = 'refund';
    /**
     * @var string The name of shipping id attribute that stores shippig id. Defaults to "null"
     */
    public $shippingIdAttribute;
    /**
     * json encoded data example:
     *   {"obj_type":"ShippingOrder",
     *    "obj_id":"3",
     *    "items":[
     *       {"135":{
     *           "amount":"3.40",
     *           "shipping_surcharge":"1.00"
     *         }
     *       },
     *       {"136":{
     *           "amount":"2.80",
     *           "shipping_surcharge":"0.00"
     *         }
     *       },
     *    ],
     *    "include_shipping_rate":"N",
     *    "expected_amount":"6.20", 
     *    "actual_amount":"6.20"
     *   }
     * 
     * @return associative array
     */
    private $_d;
    public function getRefundData() 
    {
        if ($this->_d==null)
            $this->_d = json_decode($this->getOwner()->{$this->refundDataAttribute},true);//return associative array
        return $this->_d;
    }
    /**
     * Check if has refund
     * @return array
     */
    public function hasRefund() 
    {
        return $this->getRefundData()!=null;
    }
    /**
     * Return refund object type
     * @return string
     */
    public function getRefundObjType() 
    {
        return $this->hasRefund()?$this->_d['obj_type']:null;
    }
    /**
     * Return refund object id
     * @return string
     */
    public function getRefundObjId() 
    {
        return $this->hasRefund()?$this->_d['obj_id']:null;
    }
    /**
     * Return actual refund amount
     * @return string
     */
    public function getActualRefundAmount() 
    {
        return $this->hasRefund()?$this->_d['actual_amount']:null;
    }
    /**
     * Return refund shipping rate to be included
     * @return string
     */
    public function getRefundShippingRateIncluded() 
    {
        if ($this->hasRefund())
            return isset($this->_d['include_shipping_rate'])?$this->_d['include_shipping_rate']:false;
        else
            return false;
    }    
    /**
     * Return refund item amount
     * @return array
     */
    public function getRefundSupportingInfo() 
    {
        if ($this->hasRefund())
            return isset($this->_d['supporting_info'])?$this->_d['supporting_info']:false;
        else
            return false;
    }
    /**
     * Return refund shipping orders in array
     * This is only applicable to Order
     * @return array
     */
    public function getRefundShippingOrders() 
    {
        if ($this->hasRefund())
            return isset($this->_d['shipping_orders'])?$this->_d['shipping_orders']:null;
        else
            return null;
    }
    /**
     * Return refund items in array
     * @return array
     */
    public function getRefundItems() 
    {
        return $this->hasRefund()?$this->_d['items']:null;
    }
    /**
     * Return refund item ids in array
     * @return array
     */
    public function getRefundItemIds() 
    {
        return $this->hasRefund()?array_keys($this->getRefundItems()):null;
    }    
    /**
     * Return refund item amount
     * @return array
     */
    public function getRefundItemAmount($itemId) 
    {
        return $this->hasRefund()?$this->_d['items'][$itemId]['amount']:0.00;
    }
    /**
     * Return refund item shipping surcharge
     * @return array
     */
    public function getRefundItemShippingSurcharge($itemId) 
    {
        return $this->hasRefund()?$this->_d['items'][$itemId]['shipping_surcharge']:0.00;
    }
    /**
     * Return refund item total
     * @return array
     */
    public function getExpectedRefundItemsTotal() 
    {
        $total = 0;
        if ($this->getRefundItemIds()!=null){
            foreach ($this->getRefundItemIds() as $item) {
                $total += $this->getRefundItemAmount($item);
            }
        }
        return $total;
    }
    /**
     * Return refund item shipping shipping surcharge total
     * @return array
     */
    public function getExpectedRefundItemsShippingSurchargeTotal() 
    {
        $total = 0;
        if ($this->getRefundItemIds()!=null){
            foreach ($this->getRefundItemIds() as $item) {
                $total += $this->getRefundItemShippingSurcharge($item);
            }
        }
        return $total;
    }
    /**
     * Return expected refund amount
     * The formula is: grant_total - shipping_rate (if to be excluded)
     * Note: grant_total already includes all tax and discounts and shipping rate
     * @return string
     */
    public function getExpectedRefundAmount() 
    {
        if ($this->hasRefund()){
            if ($this->getRefundShippingRateIncluded())
                return $this->getOwner()->grand_total;
            else
                return $this->getOwner()->grand_total - $this->getOwner()->getShippingRate();
        }
        else
            return 0.0;
    }
    /**
     * This amount is computed based on actual item refund 
     */
    public function getComputedRefundItemsTotal()
    {
        $total = 0.0;
        foreach ($this->getOwner()->items as $item) {
            if (isset($item->actualRefundAmount))
                $total += $item->actualRefundAmount;
        }
        return $total;
    }
    /**
     * Retreive the refund total;
     * It first try to see if owner has actual refund data
     * If not, it will compute it from item refund
     * @return type
     */
    public function getRefundTotal()
    {
        $total = $this->getActualRefundAmount();
        if (!isset($total))
            $total = $this->getComputedRefundItemsTotal();
        return $total;
    }
    
}
