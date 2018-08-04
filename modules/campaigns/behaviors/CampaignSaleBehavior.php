<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CampaignSaleBehavior
 *
 * Pre-requisite: It requires DiscountableBehavior to be attached
 * 
 * Internally stores json decoded data elements:
 * <pre>
 * ...
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
 * ...
 * </pre>
 * 
 * @see DiscountableBehavior for full data elements 
 * @author kwlok
 */
class CampaignSaleBehavior extends CActiveRecordBehavior 
{
    private $_d;
    /**
     * Return all discount data
     * @return array
     */
    public function getCampaignSaleData() 
    {
        if ($this->_d==null)
            $this->_d = $this->getOwner()->getDiscountCampaign(CampaignSale::model());
        return $this->_d;
    }
    /**
     * Check if has campaign sale
     * @return array
     */
    public function hasCampaignSale() 
    {
        return $this->getOwner()->hasDiscount(CampaignSale::model());
    }    
    public function getCampaignSaleDiscount() 
    {
        return $this->getOwner()->getDiscountValue(CampaignSale::model());
    }    
    public function getCampaignSaleTip() 
    {
        return $this->getOwner()->getDiscountTip(CampaignSale::model());
    }    
    public function getCampaignSaleDiscountText() 
    {
        return $this->getOwner()->getDiscountText(CampaignSale::model());
    }      
    public function getCampaignSaleValue() 
    {
        return $this->hasCampaignSale()?$this->getCampaignSaleData()->sale_value:null;
    }    
    public function getCampaignSaleType() 
    {
        return $this->hasCampaignSale()?$this->getCampaignSaleData()->sale_type:null;
    }    
    public function getCampaignSaleOfferValue() 
    {
        return $this->hasCampaignSale()?$this->getCampaignSaleData()->offer_value:null;
    }    
    public function getCampaignSaleOfferType() 
    {
        return $this->hasCampaignSale()?$this->getCampaignSaleData()->offer_type:null;
    }    
    public function getCampaignSaleOfferTag($forceLoad=false) 
    {
        return ($this->hasCampaignSale()||$forceLoad)?$this->getCampaignSaleData()->offer_tag:null;
    }    
    public function getCampaignSaleText($locale=null,$forceLoad=false) 
    {
        if ($this->hasCampaignSale()||$forceLoad){
            $text = $this->getCampaignSaleData()->text;
            if (is_scalar($text)){
                return $text;
            }    
            else {
                return $this->getOwner()->parseLanguageValue($text,$locale);
            }
        }
        else
            return null;
    }    
    public function getCampaignSaleColorTag($locale=null,$showLink=true,$linkHtmlOptions=null) 
    {
        if ($this->hasCampaignSale()||$this->getOwner()->hasDiscountFreeShipping()){
            $tag = $this->getCampaignSaleOfferTag(true);
            if ($showLink){
                $model = $this->_loadModel();
                if ($model!=null)
                    $tag = CHtml::link($tag, $model->viewUrl,$linkHtmlOptions);
            }
            $tooltip = Yii::app()->controller->stooltipWidget($this->getCampaignSaleText($locale,true),array('position'=>SToolTip::POSITION_TOP),true);
            return ' '.Helper::htmlColorTag($tag,'orange').' '.$tooltip;
        }
        else
            return '';//empty tag
    }    
    private function _loadModel()
    {
        $type = $this->getCampaignSaleData()->type;
        return $type::model()->findByPk($this->getCampaignSaleData()->id);
    }
    
}
