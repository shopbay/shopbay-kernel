<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
Yii::import("common.modules.payments.behaviors.PaymentMethodBehavior");
/**
 * This is the model class for table "s_payment_method".
 *
 * The followings are the available columns in table 's_payment_method':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property integer $name
 * @property integer $method
 * @property string $params
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class PaymentMethod extends Transitionable
{
    const UNDEFINED               = -1;
    //const WALLET                = 1;//reserved for future use to store credit
    const PAYPAL_EXPRESS_CHECKOUT = 2;
    const BRAINTREE_CREDITCARD    = 3;
    const BRAINTREE_PAYPAL        = 4;
    //value bigger than 100 are all offline payment methods
    const OFFLINE_PAYMENT         = 100;
    const ATM_CASH_BANK_IN        = 101;
    const CASH_ON_DELIVERY        = 102;
    const OTHERS                  = 199;
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return PaymentConfig the static model class
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
        return 's_payment_method';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Payment Method|Payment Methods',[$mode]);
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
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
            ],                  
            'locale' => [
              'class'=>'common.components.behaviors.LocaleBehavior',
            ],              
            'transition' => [
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::PAYMENT_METHOD_ONLINE,
                'inactiveStatus'=>Process::PAYMENT_METHOD_OFFLINE,
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
            'paymentmethodbehavior' => [
                'class'=>'PaymentMethodBehavior',
            ],
        ];
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id, name, method, params', 'required'],
            ['account_id, shop_id, method', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 50 chars.
            ['name', 'length', 'max'=>1000],
            //This column stored json encoded params in different languages.
            //It buffers about 20 languages
            ['params', 'length', 'max'=>5000],
            ['status', 'length', 'max'=>10],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'],

            //on deactivate scenario, id field here as dummy
            ['status', 'ruleDeactivation','params'=>[],'on'=>'deactivate'],

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            ['shop_id, method, params, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Deactivation Check
     * (1) Verify that need shop that is online must have at least one online payment method
     */
    public function ruleDeactivation($attribute,$params)
    {
        $criteria = new CDbCriteria();
        $criteria->addNotInCondition('id', [$this->id]);
        if ($this->shop->online() && $this->shop->searchPaymentMethods(Process::PAYMENT_METHOD_ONLINE,$criteria)->totalItemCount < 1)
            $this->addError('status',Sii::t('sii','Shop is currently online and requires at least one online payment method.'));
    }        
    /**
     * Validate if payment method has any associations
     * (1) Payment method is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
           $this->addError('id',Sii::t('sii','"{object}" must be offline',['{object}'=>PaymentMethod::getName($this->method)]));
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
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
            'name' => Sii::t('sii','Name'),
            'method' => Sii::t('sii','Method'),
            'params' => Sii::t('sii','Params'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    
    public function shopAndMethod($shop,$method) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['shop_id'=>$shop]);
        $criteria->addColumnCondition(['method'=>$method]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    } 
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('method',$this->method);
        $criteria->compare('params',$this->params,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('paymentMethod/view/'.$this->id);
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
        return url('tasks/paymentMethod/'.strtolower($action));
    }     
    /**
     * Return all payment method names, including offline method name
     * @param type $method
     * @return type
     */
    public static function getName($method,$locale=null)
    {
        $names = self::getNames($locale);
        if (isset($names[$method]))
            return $names[$method];
        else {
            return self::getOfflineName($method,$locale);
        }
    }      
    /**
     * Get all payment method display names (enabled)
     * @param type $locale
     * @return type
     */
    public static function getNames($locale=null)
    {
        $names = [];
        foreach(Yii::app()->getModule('payments')->getPlugins() as $paymentMethod => $plugin){
            if ($plugin['enable'])
                $names[$paymentMethod] = Sii::tl('sii',$plugin['displayName'],$locale);
        }
        return $names;
    }
    /**
     * Return subset of payment method names (offline name only)
     * @param type $method
     * @return type
     */
    public static function getOfflineName($method,$locale=null)
    {
        $names = self::getOfflineNames($locale);
        if (isset($names[$method]))
            return $names[$method];
        else {
            return null;
        }
    }        
    /**
     * Get offline paymenet method display names
     * @param type $locale
     * @return type
     */
    public static function getOfflineNames($locale=null)
    {
        $names = [];
        foreach(Yii::app()->getModule('payments')->getPlugins() as $paymentMethod => $plugin){
            if ($paymentMethod==PaymentMethod::OFFLINE_PAYMENT){
                foreach($plugin['methods'] as $paymentMethod => $name){
                    $names[$paymentMethod] = Sii::tl('sii',$name,$locale);
                }        
            }
        }
        return $names;
    }
    
    public static function getRefundPaymentMethods($method=null) 
    {
        if (!isset($method)){
            return [
                   self::getName(self::ATM_CASH_BANK_IN)=>self::getName(self::ATM_CASH_BANK_IN),
                   self::getName(self::OTHERS)=>self::getName(self::OTHERS),
                ];
        }
        else {
            $methods = self::getRefundPaymentMethods();
            return $methods[$method];
        }
    }    
    
    public static function getFormInstance($method,$scenario=null)
    {
        $formName = Yii::app()->getModule('payments')->getPlugin($method,'form',false);
        $instance = new $formName(Helper::NULL,$scenario);
        $instance->method = $method;
        return $instance;
    }
    
}
