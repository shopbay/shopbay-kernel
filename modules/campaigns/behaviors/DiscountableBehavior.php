<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of DiscountableBehavior
 * 
 * It has the basic json decoded data elements:
 * <pre>
 * {"total":-16.8,
 *  "has_sale":true,
 *  "sale_data":
 *    {"offer_price":22.4,
 *     "has_offer":true,
 *     "discount":-5.6,
 *     "discount_text":"($5.60)",
 *     "campaign":
 *        {"id":"4",
 *         "shop":"3",
 *         "type":"CampaignSale",
 *         "name":"Storewide 20% discount for $20 above",
 *         "sale_value":"20.00",
 *         "sale_type":"A",
 *         "offer_value":"20",
 *         "offer_type":"P",
 *         "offer_tag":"20%\u6298\u6263",
 *         "validity":"Valid from 2014-10-31 to 2015-01-31",
 *         "text":"\u5168\u573a20%\u6298\u6263,\u6700\u4f4e\u8d2d\u4e70\u989d$20.00"
 *        }
 *     },
 *  "has_promo":true,
 *  "promo_data":
 *    {"promocode":"HELLOPROMO",
 *     "offer_price":11.2,
 *     "has_offer":true,
 *     "discount":-11.2,
 *     "discount_text":"($11.20)",
 *     "discount_tip":"HELLOPROMO \u4f18\u60e0\u7801\u4fc3\u9500\u6d3b\u52a8",
 *     "campaign":
 *       {"id":"3",
 *        "shop":"3",
 *        "type":"CampaignPromocode",
 *        "code":"HELLOPROMO",
 *        "offer_value":"50",
 *        "offer_type":"P",
 *        "validity":"Valid from 2015-01-03 to 2015-02-10",
 *        "text":"50%\u6298\u6263"
 *       }
 *    }
 *    "free_shipping":false,
 *    "shipping_data":
 *      {"free":false,
 *       "by_sale":false,
 *       "by_promo":false
 *      }
 *    }
 *  } 
 * </pre>
 * 
 * @see DiscountData
 * @see CampaignManager::checkShopSalePrice
 * @see CampaignManager::checkShopPromocodePrice
 * 
 * @author kwlok
 */
class DiscountableBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of discount attribute that stores json encoded discount data. Defaults to "discount"
    */
    public $discountAttribute = 'discount';
    /*
     * Internally used json encoded data 
     */
    private $_d;
    /**
     * Return all discount data
     * @return array
     */
    public function getDiscountData() 
    {
        if ($this->_d==null)
            $this->_d = json_decode($this->getOwner()->{$this->discountAttribute},true);//return associative array
            
        return $this->_d;
    }
    /**
     * Check if has discount
     * @return array
     */
    public function hasDiscount($campaign=null) 
    {
        if ($campaign==null)
            return $this->getDiscountData()!=null;
        else {
            return $this->hasDiscount()?$this->_d[$this->_parseCampaignBoolean($campaign)]:false;
        }
    }    
    public function getDiscountTotal() 
    {
        return $this->hasDiscount()?$this->_d['total']:false;
    }    
    public function getDiscountValue($campaign) 
    {
        return $this->hasDiscount()?$this->_d[$this->_parseCampaignData($campaign)]['discount']:false;
    }    
    public function getDiscountText($campaign) 
    {
        return $this->hasDiscount()?$this->_d[$this->_parseCampaignData($campaign)]['discount_text']:false;
    }    
    public function getDiscountTip($campaign) 
    {
        return $this->hasDiscount()?$this->_d[$this->_parseCampaignData($campaign)]['discount_tip']:false;
    }    
    public function hasDiscountFreeShipping() 
    {
        if ($this->hasDiscount())
            return isset($this->_d['free_shipping'])?$this->_d['free_shipping']:false;
        else 
            return false;
    }    
    
    public function getDiscountShippingData() 
    {
        if ($this->hasDiscountFreeShipping())
            return isset($this->_d['shipping_data'])?$this->_d['shipping_data']:null;
        else 
            return false;        
    }    
    
    public function getDiscountFreeShippingTip()
    {
        if ($this->hasDiscountFreeShipping()){
            if ($this->discountShippingData['by_sale'])
                return $this->getOwner()->getCampaignSaleTip();
            elseif ($this->discountShippingData['by_promo'])
                return $this->getOwner()->getCampaignPromocodeTip();
        }
        else
            return null;
    }
    
    public function getDiscountFreeShippingOfferTag($locale)
    {
        if ($this->hasDiscountFreeShipping()){
            if ($this->discountShippingData['by_sale'])
                return $this->getOwner()->getCampaignSaleOfferTag(true);
            elseif ($this->discountShippingData['by_promo'])
                return $this->getOwner()->getCampaignPromocodeCode(true).' '.$this->getOwner()->getCampaignPromocodeText($locale,true);
        }
        else
            return null;
    }
    
    public function getDiscountFreeShippingDiscountText()
    {
        if ($this->hasDiscountFreeShipping()){
            if ($this->discountShippingData['by_sale'])
                return $this->getOwner()->getCampaignSaleDiscountText();
            elseif ($this->discountShippingData['by_promo'])
                return $this->getOwner()->getCampaignPromocodeDiscountText();
        }
        else
            return null;
    }
    
    public function getDiscountFreeShippingColorTag($locale,$showLink=false)
    {
        if ($this->hasDiscountFreeShipping()){
            logTrace(__METHOD__,$this->discountShippingData);
            if ($this->discountShippingData['by_sale'])
                return $this->getOwner()->getCampaignSaleColorTag($locale,$showLink);
            elseif ($this->discountShippingData['by_promo'])
                return $this->getOwner()->getCampaignPromocodeColorTag($locale,$showLink);
        }
        else
            return null;
    }

    public function getDiscountCampaign($campaign) 
    {
        $data = $this->_parseCampaignData($campaign);
        if ($this->hasDiscount() && isset($this->_d[$data]['campaign']))
            return (object)$this->_d[$data]['campaign'];
        else 
            return false;
    }    
    
    private function _parseCampaignData($campaign)
    {
        if ($campaign instanceof CampaignSale)
            return 'sale_data';
        else if ($campaign instanceof CampaignPromocode)
            return 'promo_data';
        else
            throw new CException(Sii::t('sii','Invaild campaign'));
    }
    private function _parseCampaignBoolean($campaign)
    {
        if ($campaign instanceof CampaignSale)
            return 'has_sale';
        else if ($campaign instanceof CampaignPromocode)
            return 'has_promo';
        else
            throw new CException(Sii::t('sii','Invaild campaign'));
    }
    
}
