<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
/**
 * Description of Campaign
 *
 * @author kwlok
 */
abstract class Campaign extends Transitionable implements ICampaign 
{
    /*
     * List of campaign type supported
     */
    const BGA           = 'bga';
    const SALE          = 'sale';
    const PROMOCODE     = 'promocode';
    /*
     * List of campaign offer type supported
     */
    const OFFER_FREE          = 'F';
    const OFFER_PERCENTAGE    = 'P';
    const OFFER_AMOUNT        = 'A';    
    const OFFER_FREE_SHIPPING = 'S';
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'name' => Sii::t('sii','Name'),
            'description' => Sii::t('sii','Description'),
            'image' => Sii::t('sii','Image'),
            'shop_id' => Sii::t('sii','Shop'),
            'start_date' => Sii::t('sii','Start Date'),
            'end_date' => Sii::t('sii','End Date'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create date'),
            'update_time' => Sii::t('sii','Update date'),
            'validity' => Sii::t('sii','Validity'),//non db field, for labeling only
            'type' => Sii::t('sii','Campaign Type'),//non db field, for labeling only
        );
    }
    /**
     * Validate offer amount
     */
    public function ruleOfferAmount($attribute,$params)
    {
        if ($this->$attribute==null)
            $this->addError($attribute, Sii::t('sii','{offer} amount cannot be blank.',array('{offer}'=>$this->getAttributeLabel($attribute))));
        else {
            if(!is_numeric($this->$attribute)){
                $this->addError($attribute,Sii::t('sii','{offer} amount must be a number.',array('{offer}'=>$this->getAttributeLabel($attribute))));
                return;
            }
            else {
                if ($attribute=='offer_value' && $this->offer_type==self::OFFER_FREE_SHIPPING){
                    if($this->$attribute != 0){
                        $this->addError($attribute,Sii::t('sii','{offer} amount must be zero for free shipping offer.',array('{offer}'=>$this->getAttributeLabel($attribute))));
                    }
                }
                else {
                    if($this->$attribute < $params['min']){
                        $this->addError($attribute,Sii::t('sii','{offer} amount is too small (minimum is {min}).',array('{offer}'=>$this->getAttributeLabel($attribute),'{min}'=>$params['min'])));
                    }
                    if($this->$attribute > $params['max']){
                        $this->addError($attribute,Sii::t('sii','{offer} amount is too big (maximum is {max}).',array('{offer}'=>$this->getAttributeLabel($attribute),'{max}'=>$params['max'])));
                    }                
                }
            }
        }
    }         
    
    public function getId() 
    {
        return $this->id;
    }
    public function getAccountId() 
    {
        return $this->account_id;
    }

    public function getShopId() 
    {
        return $this->shop_id;
    }

    public function getName() 
    {
        return $this->name;
    }
    public function getValidityText($prefix=null)
    {
        $text = Sii::t('sii','Valid from {start_date} to {end_date}',array('{start_date}'=>$this->start_date,'{end_date}'=>$this->end_date));
        if (isset($prefix))
            $text = $prefix.' '.$text;
        return $text;
    }
    
    public function notExpired() 
    {
        $condition = '\''.Helper::getMySqlDateFormat(time()).'\' between start_date AND end_date';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        return $this;
    }

    public function wentExpired() 
    {
        $condition = '\''.Helper::getMySqlDateFormat(time()).'\' > end_date';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        return $this;
    }
    
    public function hasExpired() 
    {
        return $this->end_date < Helper::getMySqlDateFormat(time());
    }
    
    protected function getOfferTypeList($types=array())
    {
        if (empty($types)){
            return array(
                self::OFFER_FREE => Sii::t('sii','Free'),
                self::OFFER_PERCENTAGE => Sii::t('sii','% Offer'),
                self::OFFER_AMOUNT => Sii::t('sii','$ Offer'),
                self::OFFER_FREE_SHIPPING => Sii::t('sii','Free Shipping'),
            );
        }
        else {
            $offer =  new CMap();
            $offerTypes =  $this->getOfferTypeList();
            foreach ($types as $type) {
                if (isset($offerTypes[$type]))
                    $offer->add($type,$offerTypes[$type]);
            }
            return $offer->toArray();
        }
    }    
        
    public function getIsFreeShipping()
    {
        return $this->offer_type == Campaign::OFFER_FREE_SHIPPING;
    }
    
    public function getExpiredTag()
    {
        return Helper::htmlColorTag(Sii::t('sii','Expired'),'gray',false);
    }
    abstract public function getType();
    abstract public function getTypeColor();
    abstract public function getCampaignText();

}