<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import("common.modules.plans.models.PlanItem");
Yii::import("common.modules.plans.models.Feature");
Yii::import("common.services.workflow.models.Administerable");
/**
 * This is the model class for table "s_subscription_plan".
 *
 * The followings are the available columns in table 's_subscription_plan':
 * @property integer $id
 * @property integer $shop_id
 * @property integer $subscription_id
 * @property string $subscription_no
 * @property integer $plan_id
 * @property string $item_name
 * @property string $item_params
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class SubscriptionPlan extends SActiveRecord
{
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
        return Sii::t('sii','Subscrition Plan|Subscrition Plan',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_subscription_plan';
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
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['shop_id, subscription_id, subscription_no, plan_id, item_name', 'required'],
            ['shop_id, subscription_id, plan_id', 'numerical', 'integerOnly'=>true],
            ['subscription_no', 'length', 'max'=>25],
            ['item_name, item_params', 'length', 'max'=>500],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'subscription' => [self::BELONGS_TO, 'Subscription', 'subscription_id'],
            'plan' => [self::BELONGS_TO, 'Plan', 'plan_id'],
        ];
    }
    /**
     * Find plan item by name
     * All key attributes must present as a shop can have plans/subscriptions with same id but some plan/subscription id are already cancelled or expired etc.
     * @param type $shopId
     * @param type $subscriptionId
     * @param type $planId
     * @param type $itemName
     * @param type $partialMatch Set to true to if search item name using 'LIKE' operator
     * @return \SubscriptionPlan
     */
    public function locateItem($shopId,$subscriptionId,$planId,$itemName,$partialMatch=false) 
    {
        $criteria = new CDbCriteria();
        $criteria->compare('item_name', $itemName, $partialMatch);
        $criteria->addColumnCondition([
            'shop_id'=>$shopId,
            'subscription_id'=>$subscriptionId,
            'plan_id'=>$planId,
        ]);
        $this->getDbCriteria()->mergeWith($criteria);
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    }        
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'shop_id' => Sii::t('sii','Shop'),
            'subscription_id' => Sii::t('sii','Subscription Id'),
            'subscription_no' => Sii::t('sii','Subscription No'),
            'plan_id' => Sii::t('sii','Plan'),
            'item_name' => Sii::t('sii','Item Name'),
            'item_params' => Sii::t('sii','Item Params'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    /**
     * Read item params by field
     * @param type $field
     * @return type
     */
    public function getItemParam($field)
    {
        $params = json_decode($this->item_params,true);
        return isset($params[$field])?$params[$field]:null;
    }
    /**
     * The view url is used in admin app for administration
     * @return type
     */
    public function getViewUrl() 
    {
        return url('configs/shopplan/view/'.$this->id);
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('subscription_id',$this->subscription_id);
        $criteria->compare('subscription_no',$this->subscription_no,true);
        $criteria->compare('plan_id',$this->plan_id);
        $criteria->compare('item_name',$this->item_name,true);
        $criteria->compare('item_params',$this->item_params,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
            'sort'=>false,
        ]);
    }     
    /**
     * First [1] Check local (shop) plan item first before fall back to master plan item
     * [2] Fall back to master plan item check if not found any from local plan
     * 
     * @param type $subscription
     * @param type $itemName
     * @return type
     */
    public static function findItem($subscription, $itemName)
    {
        $planItem = SubscriptionPlan::model()->locateItem($subscription->shop_id,$subscription->id,$subscription->plan_id,$itemName,true)->find();
        if ($planItem!=null){//found!
            logTrace(__METHOD__.' Shop plan item found!',$planItem->attributes);
            if ($planItem->plan->isApproved)//the master plan must also be approved
                return $planItem;
        }
        else {
            $planItem = static::_findMasterPlanItem($subscription->plan_id, $itemName);
            if ($planItem!=null && $planItem->isSubscribable){
                logTrace(__METHOD__.' Master plan item found!',$planItem->attributes);
                return $planItem;
            }
        }
        return null;//nothing found
    }
    /**
     * Find item feature param
     * Default search into shop plan first, if not found then fall back to master plan 
     * @param SubscriptionPlan|PlanItem $planItem
     * @param type $field
     * @return boolean
     */
    public static function findItemParam($planItem, $field) 
    {
        $value = null;
        if ($planItem instanceof SubscriptionPlan){
            $value = $planItem->getItemParam($field);
            if (isset($value))
                logTrace(__METHOD__." Shop plan item param '$field' found!",$value);
        }
        elseif ($planItem instanceof PlanItem) {
            $feature = Feature::getRecord(Feature::parseKey($planItem->name, 'name'));
            $value = $feature->getParam($field);
            if (isset($value))
                logTrace(__METHOD__." Master plan item param '$field' found!",$value);
        }
        return $value;
    }      
    /**
     * Find Master PlanItem model 
     * @param type $planId
     * @param type $itemName
     * @param type $fuzzySearch If true, will search based on LIKE operator
     * @return type
     */
    private static function _findMasterPlanItem($planId,$itemName,$fuzzySearch=false)
    {
        if ($fuzzySearch)
           $condition = 'plan_id='.$planId.' AND name LIKE \'%'.$itemName.'%\'';
        else
           $condition = 'plan_id='.$planId.' AND name=\''.$itemName.'\'';
           
        logTrace(__METHOD__.' condition',$condition);
        return PlanItem::model()->find($condition);
    }    
    
}