<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.campaigns.components.DiscountData");
/**
 * Description of OrderData
 *
 * @author kwlok
 */
class OrderData extends CComponent
{
    /*
     * Total tax value = The value is computed based on tax rate
     */
    public $tax = 0;
    public $taxRate;
    public $taxPayables;    
    public $hasSale = false;
    public $hasPromo = false;
    /*
     * Private property
     */
    private $_price;
    private $_shippingFee;
    private $_discountData;
    /**
     * Order data constructor
     * @param type $initialPrice
     * @param type $initialShippingFee
     * @param DiscountData $discountData
     * @throws CException
     */
    public function __construct($initialPrice=0,$initialShippingFee=0,$discountData=null) 
    {
        $this->_price = $initialPrice;
        $this->_shippingFee = $initialShippingFee;
        $this->setDiscountData($discountData);
    }
        
    public function getRawDiscountData()
    {
        return $this->_discountData;
    }
    
    public function getHasRawDiscountData()
    {
        return $this->_discountData != null;
    }
    
    public function getHasDiscount()
    {
        return $this->hasRawDiscountData?$this->rawDiscountData->hasDiscount():false;
    }
    /**
     * Discount total is in -ve
     * @return Total discount value = The value is computed based on CampaignSale and CampaignPromocode
     */    
    public function getDiscount()
    {
        return $this->hasDiscount?$this->rawDiscountData->total:0.0;
    }

    public function setDiscount($discount)
    {
        if ($this->hasRawDiscountData){
            $this->rawDiscountData->total = $discount;
        }
        else {
            $this->rawDiscountData = new DiscountData();
            $this->rawDiscountData->total = $discount;
        }
    }

    public function getDiscountRate()
    {
        return $this->rawDiscountData->getRate($this->_price);
    }
    
    public function setDiscountData($discountData)
    {
        if ($discountData!=null && !$discountData instanceof DiscountData)
            throw new CException('Invalid DiscountData');
        $this->_discountData = $discountData;
        //logTraceDump(__METHOD__.' ',$this->_discountData);
    }
    
    public function getDiscountData()
    {
        return $this->rawDiscountData->packageData();
    }
    
    public function getDiscountSaleData()
    {
        return $this->hasRawDiscountData?(object)$this->rawDiscountData->sale_data:null;
    }

    public function getDiscountPromoData()
    {
        return $this->hasRawDiscountData?(object)$this->rawDiscountData->promo_data:null;
    }

    public function getDiscountShippingData()
    {
        return $this->hasRawDiscountData?(object)$this->rawDiscountData->shipping_data:null;
    }
    
    public function getOnFreeShipping()
    {
        return $this->hasRawDiscountData?$this->rawDiscountData->free_shipping:false;
    }
    
    public function getFreeShippingTip()
    {
        if ($this->rawDiscountData->shipping_data['by_sale'])
            return $this->discountSaleData->discount_tip;
        elseif ($this->rawDiscountData->shipping_data['by_promo'])
            return $this->discountPromoData->discount_tip;
        else
            return null;
    }
    
    public function getFreeShippingDiscountText()
    {
        if ($this->rawDiscountData->shipping_data['by_sale'])
            return $this->discountSaleData->discount_text;
        elseif ($this->rawDiscountData->shipping_data['by_promo'])
            return $this->discountPromoData->discount_text;
        else
            return null;
    }
    
    public function setPriceBeforeDiscount($price)
    {
        $this->_price = $price;
    }
    
    public function getPriceBeforeDiscount()
    {
        return $this->_price;
    }
    
    public function getPriceAfterDiscount()
    {
        return $this->priceBeforeDiscount + $this->discount;//discount total is in -ve
    }    
    
    public function getPriceAfterTax()
    {
        return $this->priceAfterDiscount + $this->tax;
    }    
    
    public function getTaxData()
    {
        return json_encode($this->taxPayables);
    }    
    
    public function setShippingFeeBeforeDiscount($fee)
    {
        $this->_shippingFee = $fee;
    }
    
    public function getShippingFeeBeforeDiscount()
    {
        return $this->_shippingFee;
    }
    
    public function getShippingFeeAfterDiscount()
    {
        return $this->onFreeShipping?0.0:$this->_shippingFee;
    }
    /**
     * 100 means 100%, 0 means 0%, 0.5 means 50%
     */
    public function getShippingFeeDiscountRate()
    {
        return $this->onFreeShipping?1:0;
    }    
    /**
     * @return Grand total price = sum of PRICE (already included SHIPPING_SURCHARGE) + SHIPPING_RATE - DISCOUNT (if any) + TAX
     */
    public function getGrandTotal()
    {
        return $this->onFreeShipping?$this->priceAfterTax:($this->priceAfterTax + $this->_shippingFee);
    }
    
    public function toArray()
    {
        return array(
            'discount' => $this->discount,
            'tax' => $this->tax,
            'grand_total' => $this->grandTotal,
            'free_shipping' => $this->onFreeShipping,
            'has_sale' => $this->hasSale,
            'sale_data' => $this->discountSaleData,
            'has_promo' => $this->hasPromo,
            'promo_data' => $this->discountPromoData,
            'shipping_data' => $this->discountShippingData,
        );
    }
    
}
