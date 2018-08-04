<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.campaigns.models.Campaign");
Yii::import("common.modules.campaigns.models.ICampaign");
/**
 * This is the model class for table "s_campaign_promocode".
 *
 * CampaignPromocode is a shop (and order) level campaign.
 * 
 * The followings are the available columns in table 's_campaign_promocode':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $code
 * @property string $offer_type
 * @property integer $offer_value
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 * 
 * @author kwlok
 */
class CampaignPromocode extends Campaign implements ICampaign
{
    const TAG_OFFER  = 'o';
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return CampaignBga the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_campaign_promocode';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Promocode Campaign|Promocode Campaigns',array($mode));
    }  
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => array(
                'class'=>'common.components.behaviors.TimestampBehavior',
            ),
            'account' => array(
                'class'=>'common.components.behaviors.AccountBehavior',
            ),
            'merchant' => array(
                'class'=>'common.components.behaviors.MerchantBehavior',
            ),
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
            ),              
            'transition' => array(
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::CAMPAIGN_ONLINE,
                'inactiveStatus'=>Process::CAMPAIGN_OFFLINE,
            ),
            'workflow' => array(
                'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ),        
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'code',
                'buttonIcon'=>true,
                //'iconUrlSource'=>'shop',
            ),
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),                
        );
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, shop_id, code, offer_value, offer_type, start_date, end_date', 'required'),
            array('account_id, shop_id', 'numerical', 'integerOnly'=>true),
            array('code', 'length', 'max'=>12),
            array('code', 'match', 'pattern'=>'/^[a-zA-Z0-9]+$/', 'message'=>Sii::t('sii','Promocode accepts only letters or digits.')),
            array('status', 'length', 'max'=>10),
            array('offer_value', 'length', 'max'=>8),
            array('offer_value', 'ruleOfferAmount','min'=>1,'max'=>100),
            array('start_date', 'compare','compareAttribute'=>'end_date','operator'=>'<','message'=>Sii::t('sii','Start Date must be smaller than End Date')),
            
            //on delete scenario, id field here as dummy
            array('id', 'ruleAssociations','params'=>array(),'on'=>'delete'),                    

            //activate scenario
            array('id, code, start_date, end_date, create_time', 'safe', 'on'=>'activate'),
            array('status', 'ruleActivation','on'=>'activate'),
            //deactivate scenario
            array('id, code, start_date, end_date, create_time', 'safe', 'on'=>'deactivate'),

            array('id, account_id, code, shop_id, offer_type, offer_value, start_date, end_date, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    } 
    /**
     * Validate if campaign has any associations
     * (1) Campaign is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
            $this->addError('id',Sii::t('sii','"{object}" must be offline',array('{object}'=>$this->code)));
    }
    /**
     * Activation Check
     * [1] There can only be one campaign online per shop
     * 
     * NOTE: TODO Open to multiple campaigns running at the same time, 
     *            But need to take care of priority and any conflict within multiple campagins 
     */
    public function ruleActivation($attribute,$params)
    {
        if ($this->hasExpired()){
            $this->addError('status',Sii::t('sii','You cannot activate campaign when it has already expired.'));
            return;
        }
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
            'shop' => array(self::BELONGS_TO, 'Shop', 'shop_id'),
        );
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'code' => Sii::t('sii','Promotional Code'),
            'offer_value' => Sii::t('sii','Offer'),
            'offer_type' => Sii::t('sii','Offer type'),
        ));
    }      
    public function shopAndCode($shop,$code) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('shop_id'=>$shop));
        $criteria->addColumnCondition(array('code'=>$code));
        $this->getDbCriteria()->mergeWith($criteria);
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    }     
    public function getName() 
    {
        return $this->code;
    }    
    public function getOfferTag($locale=null)
    {
        return $this->getCampaignText($locale,self::TAG_OFFER);
    }
    public function getCampaignText($locale=null,$text='verbose',$returnArray=false)
    {
        if ($text=='verbose'){
            $offer = new CMap();
            switch ($this->offer_type) {
                case Campaign::OFFER_PERCENTAGE:
                    $offer->add(self::TAG_OFFER,Sii::tp('sii','{offer}% off',array('{offer}'=>round($this->offer_value)),$locale));
                    break;
                case Campaign::OFFER_AMOUNT:
                    $offer->add(self::TAG_OFFER,Sii::tp('sii','{offer} off',array('{offer}'=>$this->formatCurrency(round($this->offer_value)),$locale)));
                    break;
                case Campaign::OFFER_FREE_SHIPPING:
                    $offer->add(self::TAG_OFFER,Sii::tp('sii','Free Shipping'));
                    break;
                default:
                    break;
            }
            if ($returnArray)
                return $offer;
            else 
                return $offer[self::TAG_OFFER];
        }
        else {
            $offer = $this->getCampaignText($locale,'verbose',true);
            return isset($offer[$text])?ucfirst($offer[$text]):'';
        }
    } 
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('code',$this->code,true);
        $criteria->compare('offer_value',$this->offer_value,true);
        $criteria->compare('offer_type',$this->offer_type,true);
        $criteria->compare('start_date',$this->start_date,true);
        $criteria->compare('end_date',$this->end_date,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        if ($this->getScenario()=='activate')
            $criteria->compare('status',Process::CAMPAIGN_OFFLINE);

        if ($this->getScenario()=='deactivate')
            $criteria->compare('status',Process::CAMPAIGN_ONLINE);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        logTrace(__METHOD__.' criteria',$criteria);

        return new CActiveDataProvider(
                                $this,
                                array(
                                    'criteria'=>$criteria,
                                    'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                                ));
    }    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('campaign/promocode/view/'.$this->id);
    }
    /**
     * @override
     * Custom url to work on task for this model
     * 
     * @see urlManager for mapping (main.php)
     * @see Transitionable::getTaskUrl()
     * @return string url
     */
    public function getTaskUrl($action)
    {
        return url('tasks/campaign/promocode/'.strtolower($action));
    }  
    
    public function getType() 
    {
        return $this->displayName();
    }
    public function getTypeColor() 
    {
        return 'lightsalmon';
    }
    public function getOfferTypes()
    {
        return $this->getOfferTypeList(array(
            Campaign::OFFER_PERCENTAGE,
            Campaign::OFFER_AMOUNT,
            Campaign::OFFER_FREE_SHIPPING,
         ));
    } 
    
}
