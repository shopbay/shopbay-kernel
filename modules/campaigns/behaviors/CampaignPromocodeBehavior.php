<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CampaignPromocodeBehavior
 *
 * Pre-requisite: It requires DiscountableBehavior to be attached
 * 
 * Internally stores json decoded data elements:
 * <pre>
 * ...
 *    "campaign":
 *       {"id":"3",
 *        "shop":"3",
 *        "type":"CampaignPromocode",
 *        "code":"HELLOPROMO",
 *        "offer_value":"50",
 *        "offer_type":"P",
 *        "validity":"Valid from 2015-01-03 to 2015-02-10",
 *        "text":"50%\u6298\u6263"
 *       }
 * ...
 * </pre>
 * 
 * @see DiscountableBehavior for full data elements 
 * @author kwlok
 */
class CampaignPromocodeBehavior extends CActiveRecordBehavior 
{
    private $_d;
    /**
     * Return all discount data
     * @return array
     */
    public function getCampaignPromocodeData() 
    {
        if ($this->_d==null)
            $this->_d = $this->getOwner()->getDiscountCampaign(CampaignPromocode::model());
        return $this->_d;
    }
    /**
     * Check if has campaign promocode
     * @return array
     */
    public function hasCampaignPromocode() 
    {
        return $this->getOwner()->hasDiscount(CampaignPromocode::model());
    }    
    public function getCampaignPromocodeTip() 
    {
        return $this->getOwner()->getDiscountTip(CampaignPromocode::model());
    }    
    public function getCampaignPromocodeDiscount() 
    {
        return $this->getOwner()->getDiscountValue(CampaignPromocode::model());
    }    
    public function getCampaignPromocodeDiscountText() 
    {
        return $this->getOwner()->getDiscountText(CampaignPromocode::model());
    }    
    public function getCampaignPromocodeCode($forceLoad=false) 
    {
        return ($this->hasCampaignPromocode()||$forceLoad)?$this->getCampaignPromocodeData()->code:null;
    }    
    
    public function getCampaignPromocodeOfferValue() 
    {
        return $this->hasCampaignPromocode()?$this->getCampaignPromocodeData()->offer_value:null;
    }    
    public function getCampaignPromocodeOfferType() 
    {
        return $this->hasCampaignPromocode()?$this->getCampaignPromocodeData()->offer_type:null;
    }    
    public function getCampaignPromocodeText($locale=null,$forceLoad=false) 
    {
        if ($this->hasCampaignPromocode()||$forceLoad){
            $text = $this->getCampaignPromocodeData()->text;
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
    public function getCampaignPromocodeColorTag($locale=null,$showLink=true,$linkHtmlOptions=null) 
    {
        if ($this->hasCampaignPromocode()||$this->getOwner()->hasDiscountFreeShipping()){
            $tag = $this->getCampaignPromocodeText($locale,true);
            if ($showLink){
                $model = $this->_loadModel();
                if ($model!=null)
                    $tag = CHtml::link($tag, $model->viewUrl,$linkHtmlOptions);
            }
            $tooltip = Yii::app()->controller->stooltipWidget($this->getCampaignPromocodeTip(),array('position'=>SToolTip::POSITION_TOP),true);
            return ' '.Helper::htmlColorTag($tag,'orange').' '.$tooltip;
        }
        else
            return '';//empty tag
    }    
    /**
     * Check if has campaign promocode for Free Shipping
     * @return array
     */
    public function hasCampaignPromocodeFreeShipping() 
    {
        return $this->getCampaignPromocodeOfferType()==Campaign::OFFER_FREE_SHIPPING;
    } 
    
    private function _loadModel()
    {
        $type = $this->getCampaignPromocodeData()->type;
        return $type::model()->findByPk($this->getCampaignPromocodeData()->id);
    }
    
}
