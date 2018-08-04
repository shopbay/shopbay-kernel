<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
Yii::import("common.modules.notifications.models.NotificationBatchJob");
/**
 * This is the model class for table "s_notification_subscription".
 *
 * The followings are the available columns in table 's_notification_subscription':
 * @property integer $id
 * @property integer $notification Refer to Notification->name
 * @property string $scope
 * @property integer $channel Refer to Notification->type
 * @property string $subscriber
 * @property string $account_subscriber
 * @property string $params
 * @property string $status
 * @property string $batch_status
 * @property integer $create_time
 * @property integer $update_time
 * 
 * @author kwlok
 */
class NotificationSubscription extends Transitionable
{
    use NotificationBatchJob;
    /**
     * Returns the static model of the specified AR class.
     * @return Notification the static model class
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
        return 's_notification_subscription';
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
            'transition' => [
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::NOTIFICATION_SUBSCRIBED,
                'inactiveStatus'=>Process::NOTIFICATION_UNSUBSCRIBED,
            ],
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'account_subscriber',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'account_subscriber',
            ],
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.NotificationSubscriptionWorkflowBehavior',
            ],              
        ];
    }        
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['notification, scope, channel, status', 'required'],
            ['scope', 'length', 'max'=>100],
            ['notification', 'length', 'max'=>30],
            ['channel', 'numerical', 'integerOnly'=>true],
            ['account_subscriber', 'length', 'max'=>12],
            ['subscriber', 'length', 'max'=>200],
            ['params', 'length', 'max'=>1000],
            ['status, batch_status', 'length', 'max'=>20],
            
            ['id, notification, scope, channel, account_subscriber, subscriber, params, status, batch_status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'notification' => Sii::t('sii','Notification'),
            'scope' => Sii::t('sii','Notification Scope'),
            'channel' => Sii::t('sii','Notification Channel'),
            'subscriber' => Sii::t('sii','Subscriber'),
            'account_subscriber' => Sii::t('sii','Account Subscriber'),
            'params' => Sii::t('sii','Params'),
            'status' => Sii::t('sii','Status'),
            'batch_status' => Sii::t('sii','Batch Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    /**
     * Finder method for subscribed notification
     * @return type
     */
    public function subscribed()
    {
        return $this->active();
    }
    /**
     * Finder method for unsubscribed notification
     * @return type
     */
    public function unsubscribed() 
    {
        return $this->inactive();
    }
    /**
     * Finder method for notification by name
     * @param type $name
     * @return type
     */
    public function forNotification($name) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'notification=\''.$name.'\'',
        ]);
        return $this;
    }
    /**
     * Finder method for notification channel
     * @param type $channel
     * @return type
     */
    public function notifyBy($channel) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'channel='.$channel,
        ]);
        return $this;
    }
    /**
     * inder method for notification Account scope
     * @param type $scope The scope
     * @param type $subscriber The subscriber json encoded string
     * @return type
     */
    public function scopeBy(NotificationScope $scope,$subscriber) 
    {
        $subscriberAttribute = 'subscriber';
        
        $json = json_decode($subscriber);
        
        if ($scope->isGlobal){
            $subscriberAttribute = 'account_subscriber';
        }
        elseif (isset($json->account_id)){//an login account subscription
            $subscriberAttribute = 'account_subscriber';
            $subscriber = $json->account_id;//change to account id
        }
        
        $this->getDbCriteria()->mergeWith([
            'condition'=>'scope=\''.$scope->toString().'\' AND '.$subscriberAttribute.'=\''.$subscriber.'\'',
        ]);
        return $this;
    }    
    /**
     * Get all non-event notifications based on $notification value
     * Non-event notifications are not linked to transitional object, thus the view content file cannot have $model reference param.
     * default $subscriber param is given for greeting purpose
     * @return type
     */
    public function getNonEventNotifications()
    {
        return Notification::model()->nonEvent($this->notification)->findAll();
    }
    /**
     * Get subscriber (guest, non-account)
     * @return type
     */
    public function getSubscriber() 
    {
        return json_decode($this->subscriber);
    }
    /**
     * Get subscriber email
     * Its either an account email or a guest email,and cannot be both
     * @return type
     */
    public function getSubscriberEmail() 
    {
        if (isset($this->account_subscriber) && isset($this->subscriber))
            return null;//cannot be both; so return null
        elseif (isset($this->account_subscriber))
            return $this->account->email;
        elseif (isset($this->subscriber))
            return $this->getSubscriber()->email;
        else
            return null;
    }
    /**
     * Get subscriber name
     * Its either an account name or a guest name,and cannot be both
     * @return type
     */
    public function getSubscriberName() 
    {
        if (isset($this->account_subscriber) && isset($this->subscriber))
            return null;//cannot be both; so return null
        elseif (isset($this->account_subscriber)){
            return isset($this->account->profile->name)?$this->account->profile->name:$this->account->name;
        }
        elseif (isset($this->subscriber)){
            if (isset($this->getSubscriber()->name))
                return $this->getSubscriber()->name;
            else
                return $this->getSubscriber()->email;
        }
        else
            return null;
    }
    /**
     * Get subscriber messenger psid
     * Its either an account or a guest 
     * @return type
     */
    public function getSubscriberMessenger() 
    {
        if (isset($this->account_subscriber)){
            /**
             * Expect there is field "account_messenger" inside column "subscriber"
             * @see Messenger ChatbotShop::getSubscriber()
             * @see usage at NotificationManage::_notify() for type messenger
             */
            return $this->getSubscriber()->account_messenger;
        }
        elseif (isset($this->subscriber))
            return $this->getSubscriber()->messenger;
        else
            return null;
    }
    /**
     * Get params
     * @return array
     */
    public function getParams() 
    {
        $data = json_decode($this->params,true);
        if (isset($this->subscriber)){//append subscriber data as well
            $data = array_merge($data,['subscriber'=>json_decode($this->subscriber,true)]);
        }
        //todo add account_subscriber data
        return $data;
    }
    /**
     * Overriden method
     */
    public function getViewUrl() 
    {
        //no implementation for now
    }

}