<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
Yii::import("common.services.workflow.behaviors.TransitionWorkflowBehavior");
Yii::import("common.modules.activities.behaviors.ActivityBehavior");
Yii::import("common.components.behaviors.*");
Yii::import("common.modules.shippings.behaviors.ShippingBehavior");
/**
 * This is the model class for table "s_shipping".
 *
 * The followings are the available columns in table 's_shipping':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id 
 * @property integer $zone_id
 * @property string $name
 * @property integer $method
 * @property integer $type
 * @property string $rate
 * @property integer $speed
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 * @property ShippingTier[] $shippingTiers
 *
 * @author kwlok
 */
class Shipping extends Transitionable
{
    const DEMO_SHIPPING = -1;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shipping the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Shipping|Shippings',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_shipping';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
              'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'account' =>[
              'class'=>'common.components.behaviors.AccountBehavior',
            ],            
            'merchant' => [
              'class'=>'common.components.behaviors.MerchantBehavior',
            ],     
            'locale' => [
              'class'=>'common.components.behaviors.LocaleBehavior',
            ],              
            'transition' => [
              'class'=>'common.components.behaviors.TransitionBehavior',
              'activeStatus'=>Process::SHIPPING_ONLINE,
              'inactiveStatus'=>Process::SHIPPING_OFFLINE,
            ],
            'workflow' => [
              'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ],              
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>true,
            ],
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ], 
            'shippingbehavior' => [
                'class'=>'ShippingBehavior',
            ],      
            'childbehavior' => [
                'class'=>'common.components.behaviors.ChildModelBehavior',
                'parentAttribute'=>'shipping_id',
                'childAttribute'=>'tiers',
                'childModelClass'=>'ShippingTier',
                'childUpdatableAttributes'=>['base','floor','ceiling','rate'],
            ],               
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id, zone_id, name, method, type, status', 'required'],
            ['account_id, shop_id, zone_id, method, type, speed', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 50 chars.
            ['name', 'length', 'max'=>1000],
            ['rate', 'length', 'max'=>10],
            ['status', 'length', 'max'=>10],                    
            // validate field 'type' to make sure correct rate is entered
            ['rate', 'type','type'=>'float','allowEmpty'=>true],
            ['rate', 'default', 'setOnEmpty'=>true, 'value' => null],
            ['rate', 'ruleRate'],
            //validate tiers if type is tier
            ['id', 'ruleTiers'],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'],

            //on deactivate scenario, id field here as dummy
            ['status', 'ruleDeactivation','params'=>[],'on'=>'deactivate'],

            ['id, account_id, shop_id, zone_id, name, method, type, rate, speed, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Deactivation Check
     * (1) Verify that need all products must be offline for shipping deactivation
     */
    public function ruleDeactivation($attribute,$params)
    {
        $count = 0;
        $productShippings = ProductShipping::model()->findAllByAttributes(['shipping_id'=>$this->id]);
        foreach ($productShippings as $shipping) {
            if ($shipping->product->activable())
                $count++;
        }

        if ($count < count($productShippings)){
            $this->addError('status',Sii::t('sii','At least one product associated with this shipping is not offline'));
        }
    }        
    /**
     * Validate if shipping has any associations
     * (1) Attached to any products OR
     * (2) Shipping is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
            $this->addError('id',Sii::t('sii','"{object}" must be offline',['{object}'=>$this->name]));

        if (ProductShipping::model()->exists('shipping_id='.$this->id))                
            $this->addError('id',Sii::t('sii','"{object_name}" has associations with {association_object}. Please clear the association if you wish to delete this {object_type}.',
                    ['{object_name}'=>$this->name,
                     '{association_object}'=>strtolower(Product::model()->displayName(Helper::PLURAL)),
                     '{object_type}'=> strtolower($this->displayName())]
            ));
    }
    /**
     * Validate rate
     */
    public function ruleRate($attribute,$params)
    {
        if ($this->type==Shipping::TYPE_FREE) {
            if ($this->rate===null)
                $this->addError('rate',Sii::t('sii','Rate cannot be null for Free Shipping'));
            if ($this->rate!=0)
                $this->addError('rate',Sii::t('sii','Rate must be zero for Free Shipping'));
        }
        if ($this->type==Shipping::TYPE_FLAT && $this->rate <= 0){
            if ($this->rate===null)
                $this->addError('rate',Sii::t('sii','Rate cannot be null for Flat Fee Shipping'));
            if ($this->rate<=0)
                $this->addError('rate',Sii::t('sii','Rate must be above zero for Flat Fee Shipping'));
        }
    }
    /**
     * Shipping tier validations
     * (1) Verify that need all products must be offline for shipping deactivation
     */
    public function ruleTiers($attribute,$params)
    {
        if ($this->type==Shipping::TYPE_TIERS) {
         
            $tierError = false;
            $ruleCount = 0; $prevCeiling = 0;
            foreach ($this->tiers as $tierModel) {
                if (!$tierModel->validate()){
                    if ($tierModel->hasErrors('floor'))
                        $this->addError('id',$tierModel->getError('floor'));//use id as proxy
                    if ($tierModel->hasErrors('ceiling'))
                        $this->addError('id',$tierModel->getError('ceiling'));//use id as proxy
                    if ($tierModel->hasErrors('rate'))
                        $this->addError('id',$tierModel->getError('rate'));//use id as proxy
                    $tierError = true;
                    logError('tier validation error', $tierModel->getErrors());
                }
                //check against previous rule
                if ($ruleCount>0){
                    if ($tierModel->floor <= $prevCeiling){
                        logTrace($tierModel->floor.' <= '.$prevCeiling);
                        $this->addError('id',Sii::t('sii','Floor cannot be equal or smaller than lower-tier Ceiling'));
                        $this->addError('id',$tierModel->getError('floor'));//use id as proxy
                        $tierError = true;
                        logTrace('tier validation error', $tierModel->getErrors());            
                    }
                }
                $ruleCount++;
                $prevCeiling = $tierModel->ceiling;
               
            }//end for loop
            
            if ($tierError)
                $this->addError('id',Sii::t('sii','Please correct tier rule'));            
        }
    }        
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'zone' => [self::BELONGS_TO, 'Zone', 'zone_id'],
            'tiers' => [self::HAS_MANY, 'ShippingTier', 'shipping_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'zone_id' => Sii::t('sii','Zone'),
            'name' => Sii::t('sii','Name'),
            'method' => Sii::t('sii','Shipping Method'),
            'type' => Sii::t('sii','Fee Type'),
            'rate' => Sii::t('sii','Rate per order'),
            'speed' => Sii::t('sii','ETA'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }

    public function getMethodDesc($method=null)
    {
        if ($method===null)
            $method = $this->method;
        $methods = self::getMethods();
        return $methods[$method];
    }

    public function getTypeDesc()
    {
        $types = self::getTypes();
        return $types[$this->type];
    }

    public function getTiersCount()
    {
        if ($this->type!=Shipping::TYPE_TIERS || !$this->hasTiers())
            return 0;
        else {
            return count($this->tiers);
        }
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        //$criteria->compare('id',$this->id);
        //$criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('zone_id',$this->zone_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('method',$this->method);
        $criteria->compare('type',$this->type);
        //$criteria->compare('rate',$this->rate,true);
        //$criteria->compare('speed',$this->speed);
        //$criteria->compare('status',$this->status,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider('Shipping',[
                            'criteria'=>$criteria,
                            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                        ]);

        logTrace(__METHOD__.' criteria',$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchActive($condition,$pageSize=null)
    {
        $criteria=new CDbCriteria;
        $criteria->addCondition($condition);
        $criteria->mergeWith($this->active()->getDbCriteria());
        $dataprovider = new CActiveDataProvider('Shipping',[
                            'criteria'=>$criteria,
                            'pagination'=>[
                                'pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('record_per_page'),
                            ],
                        ]);
        logTrace(__METHOD__.' criteria',$dataprovider->criteria);
        return $dataprovider;
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchMethod()
    {
        return new CActiveDataProvider($this->mine(),[
                    'criteria'=>[
                        'condition'=>'id='.$this->id,
                    ],
                    'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                    'sort'=>false,                                        
                ]);
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchTiers($order='ASC')
    {
        return new CActiveDataProvider('ShippingTier',[
                    'criteria'=>[
                        'condition'=>'shipping_id='.$this->id,
                        'order'=>'floor '.$order,
                    ],
                    'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                    'sort'=>false,                                        
                ]);
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchProductShippings($order='ASC')
    {
        return new CActiveDataProvider('ProductShipping',[
                    'criteria'=>[
                        'condition'=>'shipping_id='.$this->id,
                        'order'=>'update_time '.$order,
                    ],
                    'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                    'sort'=>false,                                        
                ]);
    }

    public function calculateRate($total=0)
    {
        if ($this->type==Shipping::TYPE_FREE)
            return 0.0;
        else if ($this->type==Shipping::TYPE_FLAT)
            return $this->rate;
        else {
            foreach ($this->searchTiers('ASC')->data as $tier){

                if ( $tier->floor <= $total){
                    //undefined or null means infinitive, so always bigger than $total
                    if ($tier->ceiling==null) $tier->ceiling = $total + 1;
                    if ($tier->ceiling >= $total)
                        return $tier->rate;
                }
            }
            return null;//unmatched, cart takes it as zero
        }
    }  
 
    /**
     * @return string Shipping description
     */
    public function getShippingDescription($surcharge=null)
    {
        switch ($this->type) {
            case self::TYPE_FLAT:
                if ($surcharge!=null||$surcharge>0)
                    return Sii::t('sii','This shipping has fixed shipping fee charged at order level regardless of how many items you purchase in one cart, plus product shipping surcharge.');
                else
                    return Sii::t('sii','This shipping has fixed shipping fee charged at order level regardless of how many items you purchase in one cart.');
            case self::TYPE_TIERS:
                $tier = $this->getTierBase();
                switch ($tier->base) {
                    case ShippingTier::BASE_SUBTOTAL:
                        if ($surcharge!=null||$surcharge>0)
                            return Sii::t('sii','This shipping has tiered shipping fee charged based on the order subtotal amount, plus product shipping surcharge. {rate_text}',['{rate_text}'=>$this->getShippingRateText(true)]);
                        else
                            return Sii::t('sii','This shipping has tiered shipping fee charged based on the order subtotal amount. {rate_text}',['{rate_text}'=>$this->getShippingRateText(true)]);
                    case ShippingTier::BASE_WEIGHT:
                        break;
                }
                return $this->getTypeDesc();
            default:
                return $this->getTypeDesc();
        }
    }     
    public function getShippingText($surcharge,$locale=null)
    {
        return Sii::tp('sii','n==0#{shipping_name} {rate}{tooltip}|n>0#{shipping_name} {rate} + surcharge {surcharge}{tooltip}',
                    [$surcharge,
                       '{shipping_name}'=>$this->displayLanguageValue('name',$locale),
                       '{rate}'=>!$this->hasTiers()?$this->formatCurrency($this->rate):'',
                       '{tooltip}'=>controller()->widget('common.widgets.stooltip.SBootstrapToolTip',[
                                        'content'=>$this->getShippingDescription($surcharge),
                                        'placement'=>'top',
                                    ],true),
                       '{surcharge}'=>$this->formatCurrency($surcharge),
                    ],$locale);

    }
    /**
     * @return mixed string or array (when tiered)
     */
    public function getShippingRateText($forceText=false,$locale=null)
    {

        if ($this->type==Shipping::TYPE_FREE)
            $text = Sii::tl('sii','Free',$locale);
        else if ($this->type!=Shipping::TYPE_TIERS || !$this->hasTiers())
            $text = $this->formatCurrency($this->rate);
        else {
            $text = new CList();
            foreach ($this->searchTiers()->data as $tier) {

                if ($tier->base==ShippingTier::BASE_SUBTOTAL)
                    $text->add(
                        Sii::tp('sii','{rate} for order subtotal from {floor} {ceiling}',[
                            '{rate}'=>$tier->rate==0?Sii::tl('sii','Free',$locale):$this->formatCurrency($tier->rate),
                            '{floor}'=>$this->formatCurrency($tier->floor),
                            '{ceiling}'=>$tier->ceiling==null?Sii::tl('sii','onwards',$locale):Sii::tp('sii','to {ceiling}',['{ceiling}'=>$this->formatCurrency($tier->ceiling)],$locale),
                        ],$locale)    
                    );
               if ($tier->base==ShippingTier::BASE_WEIGHT)
                    $text->add(
                        Sii::tp('sii','{rate} for weight subtotal from {floor} {ceiling}',[
                            '{rate}'=>$tier->rate==0?Sii::tl('sii','Free',$locale):$this->formatCurrency($tier->rate),
                            '{floor}'=>$this->formatWeight($tier->floor),
                            '{ceiling}'=>$tier->ceiling==null?Sii::tl('sii','onwards',$locale):Sii::tp('sii','to {ceiling}',['{ceiling}'=>$this->formatWeight($tier->ceiling)],$locale),
                        ],$locale)    
                    );

            }
        }
        if ($forceText){
            if ($text instanceof CList){
                $result = '';
                foreach ($text as $value) {
                    $result .= $value.'. ';
                }
                return $result;
            }
        }
        else
            return ($text instanceof CList)?$text->toArray():$text;
    }  
    
    public function getShippingRemarks($locale=null) 
    {
        $remarks = new CList();
        if ($this->type==Shipping::TYPE_TIERS){
            $remarks->add(Sii::tp('sii','Shipping fee per order charged as below: {shipping_rate_text}',['{shipping_rate_text}'=>Helper::htmlList($this->getShippingRateText(false,$locale))],$locale));
            $remarks->add(Sii::tl('sii','Above shipping fee excludes product shipping surcharge',$locale));
        }
        else
            $remarks->add(Sii::tp('sii','Shipping fee {shipping_rate_text} per order, excluding product shipping surcharge',['{shipping_rate_text}'=>$this->getShippingRateText(false,$locale)],$locale));

        if ($this->speed!=null)
            $remarks->add(Sii::tp('sii','Estimated delivery within {duration} working days',['{duration}'=>$this->speed],$locale));
        return $remarks->toArray();
    }
    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('shipping/view/'.$this->id);
    } 

    const METHOD_LOCAL_PICKUP   = 0;
    const METHOD_DELIVERY       = 1;
    const METHOD_DOWNLOAD       = 2;
    const METHOD_EMAIL          = 3;
    const METHOD_MAIL           = 4;
    const METHOD_CARRIER        = 5;

    public static function getMethods()
    {
         return [
            Shipping::METHOD_DELIVERY => Sii::t('sii','Home Delivery'),
            Shipping::METHOD_LOCAL_PICKUP => Sii::t('sii','Personal Pickup'),
            //rest methods yet to be supported...
        ];
    }
    const TYPE_FREE     = 0;
    const TYPE_FLAT     = 1;
    const TYPE_TIERS    = 2;
    const TYPE_CARRIER  = 3;

    public static function getTypes()
    {
        return [
            Shipping::TYPE_FREE => Sii::t('sii','Free Shipping'),
            Shipping::TYPE_FLAT => Sii::t('sii','Flat Fee'),
            Shipping::TYPE_TIERS => Sii::t('sii','Tiered Fee'),
            //rest types yet to be supported...
        ];
    }    
}