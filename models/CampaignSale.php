<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.campaigns.models.Campaign");
Yii::import("common.modules.campaigns.models.ICampaign");
/**
 * This is the model class for table "s_campaign_sale".
 *
 * CampaignSale is a shop (and order) level campaign.
 * 
 * The followings are the available columns in table 's_campaign_sale':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $name
 * @property string $sale_type
 * @property float $sale_value
 * @property string $offer_type
 * @property integer $offer_value
 * @property string $start_date
 * @property string $end_date
 * @property integer $image
 * @property string $description
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 * 
 * @author kwlok
 */
class CampaignSale extends Campaign implements ICampaign
{
    const TAG_SALE   = 's';
    const TAG_OFFER  = 'o';
    /*
     * List of sale type supported
     */
    const SALE_AMOUNT   = 'A';
    const SALE_QUANTITY = 'Q';
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
        return 's_campaign_sale';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Sale Campaign|Sale Campaigns',array($mode));
    }  
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'image' => array(
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'transitionMedia'=>true,
                'label'=>Sii::t('sii','Image'),
                'stateVariable'=>SActiveSession::CAMPAIGN_SALE_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_CAMPAIGN_SALE,
            ),
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
                'descriptionAttribute'=>'name',
                'buttonIcon'=>array(
                    'enable'=>true,
                ),
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
            array('account_id, shop_id, name, sale_type, sale_value, offer_value, offer_type, start_date, end_date', 'required'),
            array('account_id, shop_id, image', 'numerical', 'integerOnly'=>true),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            array('name', 'length', 'max'=>2000),
            array('offer_value', 'length', 'max'=>8),
            array('sale_value', 'length', 'max'=>8),
            array('sale_type, offer_type', 'length', 'max'=>1),
            array('status', 'length', 'max'=>10),
            array('image', 'safe'),
            array('description', 'safe'),
            array('sale_value', 'ruleSale','min'=>1,'max'=>100),
            array('offer_value', 'ruleOfferAmount','min'=>1,'max'=>100),
            array('start_date', 'compare','compareAttribute'=>'end_date','operator'=>'<','message'=>Sii::t('sii','Start Date must be smaller than End Date')),
            
            //on delete scenario, id field here as dummy
            array('id', 'ruleAssociations','params'=>array(),'on'=>'delete'),                    

            //activate scenario
            array('id, name, description, start_date, end_date, create_time', 'safe', 'on'=>'activate'),
            array('status', 'ruleActivation','on'=>'activate'),
            //deactivate scenario
            array('id, name, description, start_date, end_date, create_time', 'safe', 'on'=>'deactivate'),

            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, account_id, name, description, image, shop_id, sale_type, sale_value, offer_type, offer_value, start_date, end_date, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    } 
    /**
     * Validate sale settings
     * @see Campaign::ruleOfferAmount
     */
    public function ruleSale($attribute,$params)
    {
        if ($this->onQuantitySale()){
            if (!Helper::isInteger($this->sale_value))
                $this->addError($attribute, Sii::t('sii','Sale amount must be integer for sale {type}',array('{type}'=>$this->getSaleTypes($this->sale_type))));
        }
        $this->ruleOfferAmount($attribute, $params);
    }      
    /**
     * Validate if campaign has any associations
     * (1) Campaign is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
            $this->addError('id',Sii::t('sii','"{object}" must be offline',array('{object}'=>$this->name)));
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
        if (CampaignSale::model()->active()->exists('shop_id='.$this->shop_id))
            $this->addError('status',Sii::t('sii','There is already one sale campaign online for this shop and only one is allowed'));               
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
            'sale_type' => Sii::t('sii','Sale type'),
            'sale_value' => Sii::t('sii','Sale'),
            'offer_value' => Sii::t('sii','Offer'),
            'offer_type' => Sii::t('sii','Offer type'),
        ));
    }    
    public function getNameColumnData($locale=null) 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/campaigns/',Image::DEFAULT_IMAGE_CAMPAIGN_SALE);
        $list->add($this->displayLanguageValue('name',$locale),array(
            'image'=>$imageData,
        ));
        return $list;
    } 
    
    public function getSaleTypes($type=null)
    {
        if (isset($type)){
            $sales = $this->getSaleTypes();
            return $sales[$type];
        }
        else {
            return array(
                self::SALE_AMOUNT => Sii::t('sii','Minimum Purchased Amount'),
                self::SALE_QUANTITY => Sii::t('sii','Minimum Purchased Quantity'),
            );
        }
    }    
    public function getOfferTag($locale=null)
    {
        return $this->getCampaignText($locale,self::TAG_OFFER);
    }
    public function getCampaignText($locale=null,$text='verbose',$returnArray=false)
    {
        if ($text=='verbose'){
            $offer = new CMap();
            switch ($this->sale_type) {
                case self::SALE_AMOUNT:
                    $offer->add(self::TAG_SALE,Sii::tp('sii','minimum purchase of {amount}',array('{amount}'=>$this->formatCurrency($this->sale_value)),$locale));
                    break;
                case self::SALE_QUANTITY:
                    $offer->add(self::TAG_SALE,Sii::tp('sii','minimum purchase of {quantity} item|minimum purchase of {quantity} items',array($this->sale_value,'{quantity}'=>round($this->sale_value)),$locale));
                    break;
                default:
                    break;
            }
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
                return Sii::tp('sii','Store-wide {offer} for {sale}',array('{offer}'=>$offer[self::TAG_OFFER],'{sale}'=>$offer[self::TAG_SALE]),$locale);
        }
        else {
            $offer = $this->getCampaignText($locale,'verbose',true);
            return isset($offer[$text])?ucfirst($offer[$text]):'';
        }
    } 
    
    public function onAmountSale()
    {
        return $this->sale_type==self::SALE_AMOUNT;
    } 
    
    public function onQuantitySale()
    {
        return $this->sale_type==self::SALE_QUANTITY;
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('image',$this->image);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('sale_value',$this->salve_value,true);
        $criteria->compare('sale_type',$this->sale_type,true);
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
        return url('campaign/sale/view/'.$this->id);
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
        return url('tasks/campaign/sale/'.strtolower($action));
    }  
    
    public function getType() 
    {
        return $this->displayName();
    }
    public function getTypeColor() 
    {
        return 'pink';
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
