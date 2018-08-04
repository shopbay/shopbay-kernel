<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.campaigns.behaviors.CampaignableBehavior");
/**
 * Description of CampaignBgaBehavior
 * 
 * Internally stores json decoded data elements:
 * <pre>
 * array(
 *  'campaign_id'=>'<campaign_id>',
 *  'campaign_model'=>'<campaign_model_classname>',
 *  'campaign_name'=>'<campaign_name>',
 *  'campaign_offer_type'=>'<campaign_offer_type>',
 *  'campaign_text'=>'<campaign_text>',
 *  'campaign_usual_price'=>'<campaign_usual_price>',
 *  'campaign_offer_tag'=>'<campaign_offer_tag>',
 *  'campaign_offer_price'=>'<campaign_offer_price>',
 *  'campaign_at_offer'=>'<campaign_at_offer>',
 *  'campaign_item'=>'<campaign_item>',
 *  'affinity_key'=>'<affinity_key>',
 * );
 * </pre>
 * 
 * @see OrderItemForm::getCampaignData()
 * @author kwlok
 */
class CampaignBgaBehavior extends CampaignableBehavior 
{
    public function getCampaignItem() 
    {
        return $this->hasCampaign()?$this->getCampaignData()->campaign_item:false;
    }    
    public function isCampaignItem() 
    {
        return $this->getCampaignItem();
    }    
    public function getCampaignUsualPrice() 
    {
        return $this->hasCampaign()?$this->getCampaignData()->campaign_usual_price:false;
    }    
    public function getCampaignOfferPrice() 
    {
        return $this->hasCampaign()?$this->getCampaignData()->campaign_offer_price:false;
    }    
    public function getCampaignOfferTag($locale) 
    {
        if ($this->hasCampaign()){
            $offerTag = $this->getCampaignData()->campaign_offer_tag;
            if (is_scalar($offerTag)){
                return $offerTag;
            }    
            else {
                return $this->getOwner()->parseLanguageValue($offerTag,$locale);
            }
        }
        else
            return false;
    }    
    public function getCampaignColorTag($locale,$includeOfferTag=true) 
    {
        $tag = Helper::htmlColorTag(Sii::t('sii','Promotion Item',array(),null,$locale));
        if ($includeOfferTag)
            $tag .= ' '.Helper::htmlColorTag($this->getCampaignOfferTag($locale),'orange');
        return $tag;
    }    
    public function getAffinityKey() 
    {
        return $this->hasCampaign()?$this->getCampaignData()->affinity_key:null;
    }    
    public function hasAffinity()
    {
        return $this->getAffinityKey()!=null;
    }
    
}
