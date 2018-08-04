<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.notifications.models.NotificationTrait');
/**
 * This is the model class for table "s_notification".
 *
 * The followings are the available columns in table 's_notification':
 * @property integer $id
 * @property integer $name
 * @property integer $type
 * @property string $event_type
 * @property string $event
 * @property integer $recipient_type
 * @property string $recipient
 * @property string $subject
 * @property string $content
 * 
 * @author kwlok
 */
class Notification extends CActiveRecord
{
    use NotificationTrait;
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
        return 's_notification';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['id, name,type, event_type, recipient, recipient_type, subject, content', 'required'],
            ['id, type, recipient_type', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>30],
            ['event_type', 'length', 'max'=>20],
            ['event, recipient', 'length', 'max'=>50],
            ['subject', 'length', 'max'=>100],
            
            ['id, name, type, event_type, event, recipient, recipient_type, subject, content', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'type' => Sii::t('sii','Type'),
            'event_type' => Sii::t('sii','Event Type'),
            'event' => Sii::t('sii','Event'),
            'recipient_type' => Sii::t('sii','Recipient Type'),
            'recipient' => Sii::t('sii','Recipient'),
            'subject' => Sii::t('sii','Subject'),
            'content' => Sii::t('sii','Content'),
        ];
    }

    public function message() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'type='.Notification::$typeMessage,
        ]);
        return $this;
    }
    
    public function email() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'type='.Notification::$typeEmail,
        ]);
        return $this;
    }
    /**
     * Finder for non-event notificaiton (not linked to transitional object status)
     * @param string $name
     * @return \Notification
     */
    public function nonEvent($name=null) 
    {
        $condition = 'event_type IS NULL AND event IS NULL';
        if (isset($name))
            $condition .= ' AND name=\''.$name.'\'';
        $this->getDbCriteria()->mergeWith([
            'condition'=>$condition,
        ]);
        return $this;
    }
    /**
     * Finder for event-based notificaiton (linked to transitional object status)
     * @return \Notification
     */
    public function event($event_type,$event) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'event_type=\''.$event_type.'\' AND event=\''.$event.'\'',
        ]);
        return $this;
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('type',$this->type);
        $criteria->compare('event',$this->event,true);
        $criteria->compare('recipient',$this->recipient,true);
        $criteria->compare('recipient_type',$this->recipient_type);
        $criteria->compare('subject',$this->subject,true);
        $criteria->compare('content',$this->content,true);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }
    /**
     * Get action url based on model and domain
     * @param type $model
     * @param type $domain If not set, will use default domain for each model respectively
     * @return string
     */
    public static function getActionUrl($model,$domain=null)
    {
        $url = '#';//default no url
        if ($model instanceof Item||$model instanceof Order){
            if (isset($domain))//if domain is set, should be merchant portal domain
                $url = $model->getViewUrl($domain);
            elseif ($model->byGuestCustomer()){
                $shopDomain = $model->shop->domain;//if not null, will use shop subdomain
                $url = $model->getGuestAccessUrl($shopDomain);
            }
            else {
                if (Account::isSubType($model->account_id) && $model->shop->domain!=null){
                    //show shop sub domain url
                    $url = $model->shop->url.'/'.$model->getViewRoute();
                }
                else
                    $url = $model->getViewUrl(app()->urlManager->hostDomain);
            }
        }
        if ($model instanceof Subscription||$model instanceof ShippingOrder)
            $url = $model->getViewUrl(isset($domain)?$domain:app()->urlManager->merchantDomain);
        
        if ($model instanceof Question)
            $url = $model->getAnswerUrl(isset($domain)?$domain:app()->urlManager->merchantDomain);
        
        logTrace(__METHOD__.' url',$url);
        return $url;
    }
    /**
     * Parse the correct buttion action label according to model and user role
     * @param type $model
     * @param type $userRole The current user role
     * @return type
     */
    public static function getActionLabel($model,$userRole=null)
    {
        if ($model instanceof Question)
            return Sii::t('sii','Answer Now');        
        elseif ($model instanceof Order)
            return Sii::t('sii','{action} Now',['{action}'=>$model->isRolePermitted(isset($userRole)?$userRole:Role::CUSTOMER)?Process::getActionText($model->getWorkflowAction()):Sii::t('sii','View')]);        
        elseif ($model instanceof ShippingOrder)
            return Sii::t('sii','{action} Now',['{action}'=>$model->isRolePermitted(isset($userRole)?$userRole:Role::MERCHANT)?Process::getActionText($model->getWorkflowAction()):Sii::t('sii','View')]);
        else
            return Sii::t('sii','View Now');
    }
      
}