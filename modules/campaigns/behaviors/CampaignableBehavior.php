<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CampaignableBehavior
 *
 * The base of various campaign behavior
 * 
 * It has the basic json decoded data elements:
 * <pre>
 * array(
 *  'campaign_id'=>'<campaign_id>',
 *  'campaign_model'=>'<campaign_model_classname>',
 *  'campaign_name'=>'<campaign_name>',
 *  'campaign_offer_type'=>'<campaign_offer_type>',
 *  'campaign_text'=>'<campaign_text>',
 * );
 * </pre>
 * 
 * @author kwlok
 */
class CampaignableBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of campaign attribute that stores json encoded campaign data. Defaults to "campaign"
    */
    public $campaignAttribute = 'campaign';
    /*
     * Internally used json encoded data 
     */
    private $_d;
    /*
     * Internally used to store campaign model
     */
    private $_m;
    
    public function getCampaignData() 
    {
        if ($this->_d==null)
            $this->_d = json_decode($this->getOwner()->{$this->campaignAttribute});
        return $this->_d;
    }
    public function hasCampaign() 
    {
        return $this->getCampaignData()!=null;
    }    
    public function getCampaignModel()
    {
        if ($this->_m===null){
            if (!$this->hasCampaign())
                throw new CException(Sii::t('sii','Campaignable has no data'));
            $type = $this->getCampaignModelClass();
            $model = $type::model()->findByPk($this->getCampaignId());
            if ($model!=null){
                $this->_m = $model;
            }
        }
        return $this->_m;
    }
    public function getCampaignId() 
    {
        return $this->hasCampaign()?$this->getCampaignData()->campaign_id:null;
    }    
    public function getCampaignModelClass() 
    {
        return $this->hasCampaign()?$this->getCampaignData()->campaign_model:null;
    }    
    public function getCampaignOfferType() 
    {
        return $this->hasCampaign()?$this->getCampaignData()->campaign_offer_type:false;
    }    
    public function getCampaignText($locale=null)             
    {
        if ($this->hasCampaign()){
            $offerText = $this->getCampaignData()->campaign_text;
            if (is_scalar($offerText)){
                return $offerText;
            }    
            else {
                return $this->getOwner()->parseLanguageValue($offerText,isset($locale)?$locale:$this->getOwner()->getLocale());
            }
        }
        else
            return false;        
    }    

}
