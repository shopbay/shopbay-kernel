<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.services.workflow.models.Administerable");
Yii::import('common.modules.plans.models.SubscriptionPlan');
Yii::import('common.modules.plans.models.SubscriptionAssignment');
Yii::import('common.modules.plans.models.Plan');
Yii::import('common.modules.plans.models.Feature');
Yii::import('common.modules.billings.models.Receipt');
/**
 * This is the model class for table "s_subscription".
 *
 * The followings are the available columns in table 's_subscription':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $subscription_no
 * @property integer $package_id
 * @property integer $plan_id
 * @property date $start_date
 * @property date $end_date
 * @property string $status
 * @property string $payment_token
 * @property string $charged
 * @property string $transaction_data
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Subscription extends Administerable
{
    const CHARGED     = 'Y';
    const NOT_CHARGED = 'N';
    /**
     * Status for administerable
     * @var string 
     */
    public $suspendedStatus = Process::SUBSCRIPTION_SUSPENDED;
    public $suspendableStatus = [
                    Process::SUBSCRIPTION_ACTIVE,
                    Process::SUBSCRIPTION_PASTDUE,
                ];
    /**
     * Status to indicate soft delete
     * @var string 
     */
    protected $softDeleteStatus = [
                    Process::SUBSCRIPTION_PENDING_CANCEL,
                    Process::SUBSCRIPTION_CANCELLED,
                    Process::SUBSCRIPTION_EXPIRED,
                    Process::DELETED
                ];
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
        return Sii::t('sii','Subscription|Subscriptions',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_subscription';
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
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'createIconThumbnail'=>false,
                'location'=>'merchant',
                'objectUrlAttribute'=>'activityObjectUrl',
                'buttonIcon'=> [
                    'enable'=>true,
                ],
            ],
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.SubscriptionWorkflowBehavior',
            ], 
        ];
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, package_id, plan_id, subscription_no, start_date, end_date, status', 'required'],
            ['account_id, shop_id, package_id, plan_id', 'numerical', 'integerOnly'=>true],
            ['status', 'length', 'max'=>10],
            ['charged', 'length', 'max'=>1],
            ['transaction_data', 'length', 'max'=>5000],
            ['subscription_no', 'length', 'max'=>25],
            ['payment_token', 'length', 'max'=>25],
            //on create scenario
            ['start_date, end_date', 'type', 'type' => 'date', 'message' => '{attribute}: is not a date.', 'dateFormat' => 'yyyy-MM-dd','on'=>'create'],           
            ['start_date', 'compare','compareAttribute'=>'end_date','operator'=>'<','message'=>Sii::t('sii','Start Date must be smaller than End Date'),'on'=>'create'],
            // The following rule is used by search().
            ['id, account_id, shop_id, package_id, plan_id, subscription_no, start_date, end_date, status, payment_token, charged, transaction_data, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }   
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'package' => [self::BELONGS_TO, 'Package', 'package_id'],
            'plan' => [self::BELONGS_TO, 'Plan', 'plan_id'],
            'permissions' => [self::HAS_MANY, 'SubscriptionPlan', 'subscription_id'],
        ];
    }  
    /**
     * @return CActiveRecord free trial ones (checking time, but not status check)
     */
    public function freeTrial() 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'plan_id'=>Plan::FREE_TRIAL,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }   
    /**
     * @return CActiveRecord free plan ones (checking time, but not status check)
     */
    public function freePlan() 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'plan_id'=>Plan::FREE,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }   
    /**
     * @return CActiveRecord non expired ones (checking time, but not status check)
     */
    public function notExpired() 
    {
        $condition = '\''.Helper::getMySqlDateFormat(time()).'\' between start_date AND end_date';        
        $this->getDbCriteria()->mergeWith(['condition'=>$condition]);
        return $this;
    }   
    /**
     * @return CActiveRecord expired ones (checking time, but not status check)
     */
    public function expired($order='create_time desc') 
    {
        $condition = '\''.Helper::getMySqlDateFormat(time()).'\' > end_date';        
        $this->getDbCriteria()->mergeWith([
            'condition'=>$condition,
            'order'=>$order,
        ]);
        return $this;
    }     
    /**
     * @return CActiveRecord by subscription_no.
     */
    public function subscriptionNo($subscription_no) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'subscription_no'=>$subscription_no,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }    
    /**
     * @return CActiveRecord by billing period.
     */
    public function billingPeriod($start_date,$end_date) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'start_date'=>$start_date,
            'end_date'=>$end_date,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    }    
    /**
     * A wrapper method to return pending record of this model
     * @return \Subscription
     */
    public function pending() 
    {
        return $this->status(Process::SUBSCRIPTION_PENDING);
    }
    /**
     * A wrapper method to return pastdue record of this model
     * @return \Subscription
     */
    public function pastdue()
    {
        return $this->status(Process::SUBSCRIPTION_PASTDUE);
    }
    /**
     * A wrapper method to return suspended record of this model
     * @return \Subscription
     */
    public function suspended()
    {
        return $this->status(Process::SUBSCRIPTION_SUSPENDED);
    }
    /**
     * A wrapper method to return active record of this model
     * @return \Subscription
     */
    public function active() 
    {
        return $this->status([
            Process::SUBSCRIPTION_ACTIVE,
            Process::SUBSCRIPTION_PASTDUE,
        ]);
    }     
    /**
     * A wrapper method to return cancelled record of this model
     * @return \Subscription
     */
    public function cancelled() 
    {
        return $this->status([
            Process::SUBSCRIPTION_CANCELLED,
            Process::SUBSCRIPTION_PENDING_CANCEL,
        ]);
    }     
    /**
     * A wrapper method to return approved records of this model
     * @return \Subscription
     */
    public function myPlans($account_id,$status=null) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'account_id'=>$account_id,
        ]);
        if (isset($status)){
            $criteria->addColumnCondition([
                'status'=>$status,
            ]);        
        }
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }  
    /**
     * A wrapper method to return record of this model
     * @return \Subscription
     */
    public function myPlan($account_id,$plan_id,$status=null) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'account_id'=>$account_id,
            'plan_id'=>$plan_id,
        ]);
        if (isset($status)){
            $criteria->addColumnCondition([
                'status'=>$status,
            ]);        
        }
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }    
    /**
     * A wrapper method to return subscribed record 
     * @return \Subscription
     */
    public function mySubscribedPlan($account_id,$plan_id) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'account_id'=>$account_id,
            'plan_id'=>$plan_id,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this->status([
            Process::SUBSCRIPTION_ACTIVE,
            Process::SUBSCRIPTION_PENDING,
        ]);
    }    
    /**
     * Check if user has tried trial before
     * @return boolean
     */
    public function hasTrialBefore($account_id) 
    {
        return $this->myPlan($account_id, Plan::FREE_TRIAL)->exists();//check regardless of status
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
            'package_id' => Sii::t('sii','Package'),
            'plan_id' => Sii::t('sii','Plan'),
            'subscription_no' => Sii::t('sii','Subscription No'),
            'start_date' => Sii::t('sii','Service Start Date'),
            'end_date' => Sii::t('sii','Service End Date'),
            'status' => Sii::t('sii','Status'),
            'payment_token' => Sii::t('sii','Payment Token'),
            'charged' => Sii::t('sii','Charged'),
            'transaction_data' => Sii::t('sii','Transaction Data'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }  
    /**
     * A wrapper method to return all records of this model
     * @return \SActiveRecord
     */
    public function history() 
    {
        return $this;
    }     
    /**
     * @return boolean
     */
    public function getHasExpired() 
    {
        return time() > strtotime($this->end_date.' 23:59:59.00') || $this->status==Process::SUBSCRIPTION_EXPIRED;
    }    
    /**
     * Check if subscription can be activated
     */
    public function activable()
    {
        return $this->status==Process::SUBSCRIPTION_PENDING||$this->status==Process::SUBSCRIPTION_PASTDUE;
    }   
    /**
     * Check if subscription can be pastdued
     */
    public function pastdueable()
    {
        return $this->status==Process::SUBSCRIPTION_ACTIVE;
    }       
    /**
     * Check if subscription can be cancelled
     */
    public function cancellable()
    {
        return $this->status==Process::SUBSCRIPTION_ACTIVE;
    }
    /**
     * Check if subscription can be deactivated
     */
    public function deactivable()
    {
        return $this->status==Process::SUBSCRIPTION_PENDING_CANCEL;
    }    
    /**
     * Check if subscription is already charged
     */
    public function getIsActive()
    {
        return $this->status==Process::SUBSCRIPTION_ACTIVE;
    }      
    /**
     * Check if subscription is already past due
     */
    public function getIsPastdue()
    {
        return $this->status==Process::SUBSCRIPTION_PASTDUE;
    }  
    /**
     * Check if subscription is already charged
     */
    public function getIsCharged()
    {
        return $this->charged==self::CHARGED;
    }  
    /**
     * Check if subscription can be charged
     */
    public function chargeable()
    {
        return $this->charged!=self::CHARGED && $this->transaction_data==null;
    }       
    /**
     * Check if subscription can be expired
     */
    public function expirable()
    {
        return $this->status==Process::SUBSCRIPTION_ACTIVE;
    }       
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl($domain=null)
    {
        $route = 'subscription/view/'.$this->id;
        if (isset($domain))
            return app()->urlManager->createDomainUrl($domain,$route,true);
        else
            return url($route);//$route cannot start with "/" else host info not following current scheme
    }     
    
    public function getPastduePaymentUrl()
    {
        return url('plans/subscription/pastdue/shop/'.$this->shop_id);
    }
    /**
     * @see ShopSubscriptionFilter for its implementation to skip payment
     * @return string
     */
    public function getSkipPastduePaymentUrl()
    {
        //NOTE: the forward slash in front of skipPayment is mandatory so that url parsing at {@link ShopSubscriptionFilter} will not break;
        return $this->shop->viewUrl.'/?skipPastdue='.time();//a simple param to indicate skip and its request time
    }

    public function getActivityObjectUrl()
    {
        return $this->getViewUrl(app()->urlManager->merchantDomain);
    }
    
    public function getName()
    {
        return Sii::t('sii',$this->package->name);
    }

    public function getPlanName()
    {
        return Sii::t('sii',$this->plan->name);
    }

    public function getPlanDesc()
    {
        if ($this->plan->isFreeTrial)
            return Sii::t('sii','{n} Days {trial}',['{n}'=>$this->plan->duration,'{trial}'=>$this->name]);
        elseif ($this->plan->isRecurringCharge)
            return Sii::t('sii','{type} {name} Plan',['{type}'=>$this->plan->recurringDesc,'{name}'=>$this->name]);
        else
            return Sii::t('sii','{type} {name} Plan',['{type}'=>$this->plan->typeDesc,'{name}'=>$this->name]);
    }
    /**
     * Parse the subscription end date based on plan type and start date
     * @return string
     */
    public function parseEndDate()
    {
        if ($this->plan->onMonthly)
            return Helper::endDateCycle($this->start_date, 1);//for monthly, end date is one month later
        elseif ($this->plan->onYearly)
            return Helper::endDateCycle($this->start_date, 12);//for yearly, then end date one year (12 months) later
        elseif ($this->plan->isTrial){
            $duration = $this->plan->duration;
            return date('Y-m-d', strtotime("+$duration days"));//for trial duration 
        }
        elseif ($this->plan->isOneTimeCharge || $this->plan->isInternal){
            if ($this->plan->isFree)
                return '9999-12-31';//for free plan, end date is forever 
            else
                return Helper::endDateCycle($this->start_date, 12*1);//for monthly, end date is forever (put 1 years later)
        }            
    }
    /**
     * Update subscription data when subscription no is available
     * @param array $data Must contain fields subscription_no, start_date, and end_date
     * @return \Subscription
     * @throws CException
     */
    public function updateSubscriptionData($data=[])
    {
        logInfo(__METHOD__. " Received data",$data);
        $update = false;
        logTrace(__METHOD__. " Subscription $this->id before update attributes values",$this->attributes);
        foreach ($data as $attribute => $value) {
            if ($this->hasAttribute($attribute)){
                $this->$attribute = $value;
                logTrace(__METHOD__. " $attribute value changed to '$value'");
                $update = true;
            }
        }
        if( $update && !$this->save()) {
            logError(__METHOD__. " Could not update subscription",$this->errors);
            //this exception will rollback the transaction
            throw new CException('Could not update subscription');
        }
        return $this;
    }    
    /**
     * Binds subscription to a shop 
     * @param mixed $shop_id The pre-created shop when user subscribes to a plan
     * @return whether the model successfully saved
     */
    public function bindTo($shop_id)
    {
        $this->shop_id = $shop_id;
        if ($this->save()){
            logInfo(__METHOD__. " Subscription is bond to shop '$shop_id' successfully",$this->attributes);
            return true;
        }
        else
            return false;
    }    
    /**
     * This clones all the subscription plan features to be allocated at shop level.
     * This separate shop plan features from master plan and allows plan customization such as feature add-on
     * e.g. add extra storage, add more products (in blocks of 100?) etc
     * 
     * Subsequent master plan feature change will not affect shop level subscription
     * 
     * @param mixed $shop_id The pre-created shop when user subscribes to a plan
     * @return whether the model successfully saved
     */
    public function clonePlan()
    {
        foreach ($this->plan->items as $planItem) {
            $shopPlan = new SubscriptionPlan();
            $shopPlan->shop_id = $this->shop_id;
            $shopPlan->subscription_id = $this->id;
            $shopPlan->subscription_no = $this->subscription_no;
            $shopPlan->plan_id = $this->plan_id;
            $shopPlan->item_name = $planItem->name;
            $feature = Feature::getRecord(Feature::parseKey($planItem->name,'name'));
            if ($feature!=null)
                $shopPlan->item_params = $feature->params;
            if ($shopPlan->save())
                logInfo(__METHOD__. " Subscription plan item cloned for shop '$this->shop_id' successfully",$shopPlan->attributes);
        }
    }    
    /**
     * Delete shop. This is called when the subscription is cancelled
     * @see PlanManager::unsubscribe 
     */
    public function deleteShop()
    {
        $this->shop->delete();//soft delete
        logInfo(__METHOD__. " Subscription shop is deleted.",$this->shop->attributes);
    }    
    /**
     * Create subscribe this plan to user
     * @param integer $user
     * @param Plan $plan
     * @return \Subscription
     * @throws CException
     */
    public static function create($user,$subscriptionNo,$plan)
    {
        if (!$plan instanceof Plan)//this plan model is app\modules\v1\models\Plan
            throw new CException(Sii::t('sii','Invalid plan object'));
        
        $subscription = new Subscription('create');
        $subscription->subscription_no = $subscriptionNo;
        $subscription->account_id = $user;
        $subscription->package_id = $plan->package->id;
        $subscription->plan_id = $plan->id;
        $subscription->start_date = Helper::getMySqlDateFormat(time());
        $subscription->end_date = $subscription->parseEndDate();
        $subscription->status = Process::SUBSCRIPTION_PENDING;
        $subscription->charged = self::NOT_CHARGED;
        if( !$subscription->save()) {
            logError(__METHOD__. " Could not create subscription",$subscription->getErrors());
            //this exception will rollback the transaction
            throw new CException('Could not create subscription');
        }
        return $subscription;
    }
    /**
     * @return string the final dunning date for payment before account get suspended
     */
    public function getDunningDate()
    {
        $date = new DateTime($this->end_date);
        $dunningDate = new DateTime($date->format('Y-m-d'));
        $duration = Config::getSystemSetting('subscription_dunning_days');
        $dunningDate->modify("+$duration days");
        logTrace(__METHOD__.' last day of dunning date ',$dunningDate->format('Y-m-d'));
        return $dunningDate->format('Y-m-d');
    }
    /**
     * Return if dunning period is over
     * @return boolean
     */
    public function getIsDunningPeriodOver()
    {
        return time() > strtotime($this->dunningDate.' 23:59:59.00');
    }

    public function getHasRbacAssignment()
    {
        return SubscriptionAssignment::model()->locateRbac($this->account_id,$this->planName)->exists();
    }
    
    public function getTransactionsArray()
    {
        return json_decode($this->transaction_data,true);
    }    
    
    public function getChargedStatusText()
    {
        return ['text'=>$this->charged,'color'=>$this->isCharged?'green':'red'];
    }    

    public function getHasReceipt()
    {
        return $this->isCharged && $this->receipt!==null;
    }

    public function getReceipt()
    {
        return Receipt::model()->subscription($this->id)->find();
    }        
    /**
     * Check subscription service by Db (shortcut method) - should use api instead
     * @see apiHasService
     * @param Subscription $subscription
     * @param type $service name Can be full feature name or patternized feature name
     * @return boolean
     */
    public static function hasService($subscription,$service)
    {
        if ($subscription instanceof Subscription){
            Yii::import('common.modules.plans.models.SubscriptionPermission');
            $featureKey = SubscriptionPermission::model()->fuzzySearch($subscription->plan->name,$service);
            return $featureKey!=$service;//if found, feature key is returned; Feature key is not same value as service name
        }
        else
            return false;
    }       
    /**
     * Check subscription service by Api
     * @param string $service name Can be full feature name or patternized feature name
     * @return boolean
     */
    public static function apiHasService($service,$params=[],$returnMessage=false)
    {
        Yii::import('common.components.actions.api.ApiCheckAction');
        try {
            $action = new ApiCheckAction(Yii::app()->controller,'ApiCheckAction');
            $action->permission = $service;
            $action->refreshAccessToken = false;
            $action->throwExceptionOnRejection = true;
            $action->postFields = $params;
            Yii::app()->controller->runAction($action);
            logInfo(__METHOD__.' has access',$service);

            return $returnMessage ? $service : true;
            
        } catch (Exception $ex) {
            logError(__METHOD__.' '.$service, $ex->getMessage());
            return $returnMessage ? $ex->getMessage() : false;
        }
    }       
    /**
     * Get the latest expired subscription
     * @return Subscription
     */
    public static function findLatestExpiredSubscription($shop)
    {
        $expired = Subscription::model()->locateShop($shop)->expired('create_time desc')->findAll();
        return count($expired)<=0 ? null : $expired[0];//return the latest one, since the result order is sorted based on descending order
    }    
}
