<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.modules.plans.models.PlanTypeTrait");
Yii::import("common.modules.plans.models.MakerCheckerTrait");
Yii::import("common.modules.plans.models.SubscriptionPermission");
Yii::import("common.services.workflow.models.Administerable");
/**
 * This is the model class for table "s_plan".
 *
 * The followings are the available columns in table 's_plan':
 * @property integer $id
 * @property integer $account_id
 * @property string $name
 * @property string $type
 * @property decimal $price
 * @property string $currency
 * @property string $recurring
 * @property integer $duration
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Plan extends Administerable
{
    use PlanTypeTrait, MakerCheckerTrait;
    const FREE_TRIAL        = 1;//this is the system assign package id during installation
    const FREE              = 10;//this is the system assign package id during installation
    const LITE              = 20;//this is the system assign package id during installation
    const STANDARD_MONTHLY  = 30;//this is the system assign package id during installation
    const STANDARD_YEARLY   = 31;//this is the system assign package id during installation
    const PLUS_MONTHLY      = 40;//this is the system assign package id during installation
    const PLUS_YEARLY       = 41;//this is the system assign package id during installation
    const ENTERPRISE_MONTHLY= 50;//this is the system assign package id during installation
    const ENTERPRISE_YEARLY = 51;//this is the system assign package id during installation
    const CUSTOM            = 100;//this is the system assign package id during installation
    const TRIAL     = 'T';//Free Trial
    const FIXED     = 'F';//Fixed, One time charge
    const RECURRING = 'R';//Recurring
    const CONTRACT  = 'C';//Contract type, one time charge, time and material etc
    const MONTHLY   = 'M';//Monthly Recurring Charge
    const YEARLY    = 'Y';//Annual Recurring Charge
    protected $draftedStatus = Process::PLAN_DRAFT;
    protected $submittedStatus = Process::PLAN_SUBMITTED;
    protected $approvedStatus = Process::PLAN_APPROVED;  
    public static $freeTrial;
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Plan|Plans',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_plan';
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
            'account' => [
              'class'=>'common.components.behaviors.AccountBehavior',
            ], 
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'account',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'accountProfile',
                'localeAttribute'=>'locale',
            ],    
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.PlanWorkflowBehavior',
            ], 
            'childbehavior' => [
                'class'=>'common.components.behaviors.ChildModelBehavior',
                'parentAttribute'=>'plan_id',
                'childAttribute'=>'items',
                'childModelClass'=>'PlanItem',
                'childUpdatableAttributes'=>['name','group'],
            ],            
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, name, type, price, currency, status', 'required'],
            ['account_id, duration', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>255],
            ['name', 'ruleUnique'],
            ['type, recurring', 'length', 'max'=>1],
            ['type', 'ruleType'],
            ['recurring', 'ruleRecurring'],
            ['duration', 'numerical','min'=>1,'max'=>99],
            ['duration', 'ruleDuration'],
            ['price', 'length', 'max'=>10],
            ['price', 'type', 'type'=>'float'],
            ['price', 'numerical', 'min'=>0],
            ['currency', 'length', 'max'=>3],
            ['status', 'length', 'max'=>10],
            // The following rule is used by search().
            ['id, account_id, name, type, price, recurring, duration, currency, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }   
    /**
     * Duration validation rule
     */
    public function ruleDuration($attribute, $params)
    {
        if ($this->type==self::TRIAL && $this->$attribute==null)
            $this->addError($attribute,Sii::t('sii','Duration must be set'));
    }  
    /**
     * Recurring validation rule
     */
    public function ruleRecurring($attribute, $params)
    {
        if ($this->type==self::RECURRING){
            if ($this->$attribute==null){
                $this->addError($attribute,Sii::t('sii','Missing recurring field'));
                return;
            }
            if (!in_array($this->$attribute,array_keys(self::getRecurrings())))
                $this->addError($attribute,Sii::t('sii','Invalid recurring: {recurring}',['{recurring}'=>$this->$attribute]));
        }
    }   
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'items' => [self::HAS_MANY, 'PlanItem', 'plan_id'],
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
            'name' => Sii::t('sii','Name'),
            'type' => Sii::t('sii','Type'),
            'price' => Sii::t('sii','Price'),
            'currency' => Sii::t('sii','Currency'),
            'recurring' => Sii::t('sii','Recurring'),
            'duration' => Sii::t('sii','Duration'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('type',$this->type,true);
        $criteria->compare('price',$this->price);
        $criteria->compare('currency',$this->currency,true);
        $criteria->compare('recurring',$this->recurring,true);
        $criteria->compare('duration',$this->duration);
        //$criteria->compare('status',$this->status,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);
        $criteria->mergeWith($this->mine()->getDbCriteria());
        $dataprovider = new CActiveDataProvider('Plan',[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);
        logTrace(__METHOD__.' criteria',$dataprovider->criteria);
        return $dataprovider;
    }
    /**
     * @return CActiveDataProvider the data provider that can return plan items models.
     */
    public function searchItems()
    {
        return new CActiveDataProvider('PlanItem',[
            'criteria'=>['condition'=>'plan_id='.$this->id,'order'=>'create_time DESC'],
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
            'sort'=>false,
        ]);
    }     
    
    public function updatePermissions()
    {
        SubscriptionPermission::model()->deleteAllByAttributes(['parent'=>$this->name]);
        foreach ($this->items as $item) {
            $permission = new SubscriptionPermission();
            $permission->parent = $this->name;
            $permission->child = $item->name;
            $permission->save();
            logTrace(__METHOD__.' permission saved ok',$permission->attributes);
        }
    }    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('plan/view/'.$this->id);
    } 

    public function getIsFree()
    {
        return $this->price==0;
    }    
    
    public function chargeable()
    {
        return $this->price > 0;
    }

    public function getIsFreeTrial()
    {
        return $this->isTrial && $this->isFree;
    }    
    
    public function getOnMonthly()
    {
        return $this->isRecurringCharge && $this->recurring==self::MONTHLY;
    }

    public function getOnYearly()
    {
        return $this->isRecurringCharge && $this->recurring==self::YEARLY;
    }
    /**
     * @return boolean if has items
     */
    public function getHasItems()
    {
        return $this->items!=null;
    } 

    public function getPriceDesc($skipRecurringDesc=false)
    {
        $price = $this->formatCurrency($this->price,$this->currency);
        if ($skipRecurringDesc)
            return $price;
        else
            return $this->recurringDesc.' '.$price;
    }
    
    public function getRecurringDesc()
    {
        if ($this->recurring!=null)
            return self::getRecurrings()[$this->recurring];
        else
            return null;
    }
    
    public static function getRecurrings()
    {
        return [
            self::MONTHLY => Sii::t('sii','Monthly'), 
            self::YEARLY => Sii::t('sii','Yearly'),
        ];
    }
    
    public static function getRecurringsDesc($value)
    {
        $recurrings = self::getRecurrings();
        if (isset($recurrings[$value]))
            return $recurrings[$value];
        else 
            return Sii::t('sii','unset');
    }   
    
    public static function hasFreeTrialInstance()
    {
//        Yii::beginProfile(__METHOD__);
        $result = static::freeTrialInstance()!=null;
//        Yii::endProfile(__METHOD__);
        return $result;
    }
    
    public static function freeTrialInstance()
    {
        if (!isset(static::$freeTrial)){
            static::$freeTrial =  Plan::model()->findByPk(Plan::FREE_TRIAL);
        }
        return static::$freeTrial;
    }
    
}