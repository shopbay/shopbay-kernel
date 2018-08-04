<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.modules.plans.models.MakerCheckerTrait");
Yii::import("common.modules.plans.models.PlanTypeTrait");
Yii::import("common.services.workflow.models.Administerable");
Yii::import('common.services.workflow.behaviors.PackageWorkflowBehavior');
Yii::import('common.modules.activities.behaviors.ActivityBehavior');
/**
 * This is the model class for table "s_package".
 *
 * The followings are the available columns in table 's_package':
 * @property integer $id
 * @property integer $account_id
 * @property string $name
 * @property string $type
 * @property string $plans
 * @property string $params
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Package extends Administerable
{
    use PlanTypeTrait, MakerCheckerTrait;
    const FREE_TRIAL = 1;//this is the system assign package id during installation
    const FREE       = 10;//this is the system assign package id during installation
    const LITE       = 20;//this is the system assign package id during installation
    const STANDARD   = 30;//this is the system assign package id during installation
    const PLUS       = 40;//this is the system assign package id during installation
    const ENTERPRISE = 50;//this is the system assign package id during installation
    const CUSTOM     = 100;//this is the system assign package id during installation
    const PLAN_SEPARATOR = ',';
    protected $draftedStatus = Process::PACKAGE_DRAFT;
    protected $submittedStatus = Process::PACKAGE_SUBMITTED;
    protected $approvedStatus = Process::PACKAGE_APPROVED;   
    /**
     * Package name
     */
    public static function siiName($package,$name=null)
    {
        if ($package==Package::FREE_TRIAL)
            return Sii::t('sii','Free Trial');
        elseif ($package==Package::FREE)
            return Sii::t('sii','Free');
        elseif ($package==Package::LITE)
            return Sii::t('sii','Lite');
        elseif ($package==Package::STANDARD)
            return Sii::t('sii','Standard');
        elseif ($package==Package::PLUS)
            return Sii::t('sii','Plus');
        elseif ($package==Package::ENTERPRISE)
            return Sii::t('sii','Enterprise');
        elseif ($package==Package::CUSTOM)
            return Sii::t('sii','Custom');
        else
            return Sii::t('sii','Undefined');
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Package|Packages',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_package';
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
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'accountProfile',
                'localeAttribute'=>'locale',
            ],    
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'account',
            ],
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.PackageWorkflowBehavior',
            ], 
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, name, type, plans, status', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>255],
            ['name', 'ruleUnique'],
            ['type', 'length', 'max'=>1],
            ['type', 'ruleType'],
            ['plans', 'rulePlans'],
            ['params', 'ruleParams'],
            ['status', 'length', 'max'=>10],
            // The following rule is used by search().
            ['id, account_id, name, type, plans, params, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * plans validation rule
     */
    public function rulePlans($attribute, $params)
    {
        $recurrings = [];
        foreach (explode(self::PLAN_SEPARATOR, $this->$attribute) as $key => $value) {
            $model = Plan::model()->findByPk($value);
            if ($model==null)
                $this->addError($attribute,Sii::t('sii','Plan #{id} not found.',['{id}'=>$value]));
            if ($model!=null && !$model->isApproved)
                $this->addError($attribute,Sii::t('sii','Plan "{name}" not in approved state.',['{name}'=>$model->name]));
            if ($model!=null && $model->type!=$this->type)
                $this->addError($attribute,Sii::t('sii','Plan "{name}" is not of same type of package: {type}.',['{name}'=>$model->name,'{type}'=>$this->typeDesc]));
            if ($model!=null && $model->isRecurringCharge)//store added recurring types
                $recurrings[] =  $model->recurring;
        }
        
        if (!empty($recurrings) && !in_array($this->id,[Package::LITE,Package::FREE,Package::FREE_TRIAL])){//Package Lite etc is exempted from Yearly plan
            //Package is of recurring type, and expecting both Monthly and Yearly type
            foreach (Plan::getRecurrings() as $key => $value) {
                if (!in_array($key, $recurrings))
                    $this->addError($attribute,Sii::t('sii','Package is missing "{recurring}" recurring type.',['{recurring}'=>$value]));
            }
        }
    }      
    public function ruleParams($attribute, $params)
    {
        if (is_array($this->params)){
            $this->params = json_encode($this->params);
        }

        if (strlen($this->params)>500)
            $this->addError($attribute,Sii::t('sii','Parameters length exceed limit'));
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
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
            'plans' => Sii::t('sii','Plans'),
            'params' => Sii::t('sii','Parameters'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }    
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('package/view/'.$this->id);
    }  
    /**
     * @return boolean if has items
     */
    public function getHasPlans()
    {
        return $this->plans!=null;
    } 
    
    public function existsPlan($planId)
    {
        $plans = explode(self::PLAN_SEPARATOR, $this->plans);
        return in_array($planId, $plans);
    }
    
    public function searchPlans($status=Process::PLAN_APPROVED)
    {
        $criteria = new CDbCriteria();
        if (isset($status))
            $criteria->addColumnCondition(['status'=>$status]);
        $criteria->addInCondition('id', explode(self::PLAN_SEPARATOR, $this->plans));
        logTrace(__METHOD__.' criteria',$criteria);
        return new CActiveDataProvider('Plan',['criteria'=>$criteria]);
    }   
    /**
     * A wrapper method to return published records of this model
     * Excluding Trial type
     * @return \Package
     */
    public function published() 
    {
        return $this->approved();
    }      
    
    public function getParam($field)
    {
        if (is_string($this->params))
            $params = json_decode($this->params,true);
        else
            $params = $this->params;//if already in array format, keep it
        
        return isset($params[$field])?$params[$field]:null;
    }
    
    public function getBusinessReady()
    {
        return $this->getParam(Package::$businessReady);
    }
    
    public function getShowPricing()
    {
        return $this->getParam(Package::$showPricing);
    }
    
    public function getShowButton()
    {
        return $this->getParam(Package::$showButton);
    }    
    
    public static $businessReady = 'business_ready';
    public static $showPricing   = 'show_pricing';
    public static $showButton    = 'show_button';//display button - caption and url is handled at Controller level

    public static function getParams()
    {
        return [
            static::$businessReady=>['name'=>Sii::t('sii','Business Ready')],
            static::$showPricing=>['name'=>Sii::t('sii','Show Pricing')],
            static::$showButton=>['name'=>Sii::t('sii','Show Button')],
        ];
    }
    
    public static function getParamsName($key)
    {
        $params = self::getParams();
        if (isset($params[$key])) 
        return $params[$key]['name'];
    }
}
