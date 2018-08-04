<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_message".
 *
 * The followings are the available columns in table 's_message':
 * @property integer $id
 * @property integer $sender
 * @property string $recipient
 * @property string $content
 * @property integer $sent_time
 * @property integer $received_time
 * @property string $metadata
 *
 * @author kwlok
 */
class Message extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Account the static model class
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
        return Sii::t('sii','Message|Messages',[$mode]);
    }         
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_message';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'recipient',
                'sortAttribute'=>'send_time',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'recipient',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'account',//refer to AccountObjectBehavior
                'localeAttribute'=>'profileLocale',
            ],            
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'displaySubject',
                'buttonIcon'=>[
                    'enable'=>true,
                ],
            ],
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['sender, recipient, subject, content, send_time', 'required'],
            ['sender', 'length', 'max'=>12],
            ['recipient', 'length', 'max'=>100],
            ['subject', 'length', 'max'=>200],
            ['metadata', 'length', 'max'=>500],
            ['content', 'length', 'max'=>5000],
            
            ['id, sender, recipient, subject, content, send_time, receive_time, metadata', 'safe', 'on'=>'search'],
        ];
    }

    public function getRecipientEmail()
    {
        return $this->account->email;
    }
    
    public function getSenderAccount()
    {
        $account = Account::getAccountClass($this->sender);
        return $account::model()->findByAttributes(['id'=>$account!='Account'?Account::decodeId($this->sender):$this->sender]);
    }         

    public function getSenderName()
    {
        if ($this->hasMessageMetadata()){
            return $this->getMessageMetadata('sender_name');
        }
        else {
            $sender = $this->senderAccount->profile;
            if ($sender!=null)
                return $sender->alias;
        }
        return Sii::t('sii','Sender not found');
    }         

    public function getRecipientName()
    {
        if ($this->hasMessageMetadata()){
            return $this->getMessageMetadata('recipient_name');
        }
        else {
            $recipient = $this->account;
            if ($recipient!=null)
                return $recipient->name;
        }
        return Sii::t('sii','Recipient not found');
    }         
    /**
     * Message metadata structure:
     * array(
     *   'recipient'=>$order->shop->account_id,
     *   'recipient_name'=>$order->shop->displayLanguageValue('name',user()->getLocale()),
     *   'order_id'=>$order->id,
     *   'order_no'=>$order->order_no,
     *   'shop_id'=>$order->shop_id,
     *   'shop_name'=>$order->shop->displayLanguageValue('name',user()->getLocale()),
     *   'reference_name'=>$order->order_no,
     *   'reference_link'=>$order->viewUrl,
     * )
     * 
     * @see ManagementController::prepareCompose
     */
    public function getMessageMetadata($field=null)
    {
        if ($this->hasMessageMetadata()){
            $metadata = json_decode($this->metadata);
            if (isset($field)){
                return isset($metadata->$field)?$metadata->$field:null;
            }
            else
                return $metadata;
        }
        else
            return null;
    }
    
    public function hasMessageMetadata()
    {
        return isset($this->metadata);
    }
    /**
     * A shortcut getter method to get display (either encrypt or decrypt when applies)
     * @return type
     */
    public function getDisplaySubject()
    {
        return $this->getSubject();
    }
    
    public function getSubject()
    {
        if ($this->hasMessageMetadata())
            return $this->decryptSubject();
        else
            return $this->subject;
    }
    
    public function getContent()
    {
        if ($this->hasMessageMetadata())
            return $this->decryptContent();
        else
            return $this->content;
    }
    
    public function encryptSubject()
    {
        $this->subject = SSecurityManager::encryptData($this->subject);
        return $this->subject;
    }
    
    public function decryptSubject()
    {
        return SSecurityManager::decryptData($this->subject);
    }
    
    public function encryptContent()
    {
        $this->content = SSecurityManager::encryptData($this->content);
        return $this->content;
    }
    
    public function decryptContent()
    {
        return SSecurityManager::decryptData($this->content);
    }
    
    public function getReferenceLink($reader=null)
    {
        $who = $reader==$this->sender?'sender':'recipient';
        return $this->getMessageMetadata($who.'_reference_link');
    }    
    
    public function getReferenceName()
    {
        return $this->getMessageMetadata('reference_name');
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'sender' => Sii::t('sii','Sender'),
            'recipient' => Sii::t('sii','Recipient'),
            'subject' => Sii::t('sii','Subject'),
            'content' => Sii::t('sii','Content'),
            'send_time' => Sii::t('sii','Send Time'),
            'receive_time' => Sii::t('sii','Receive Time'),
        ];
    }
    
    public function getIsNotSystemSender()
    {
       if (Account::isSubType($this->sender))
           return true;
       else
           return $this->sender!=Account::SYSTEM;
    }
    
    public function repliable($user)
    {
        return $this->recipient==$user && $this->isNotSystemSender;
    }
    
    public function isSent($user)
    {
        return $this->sender==$user;
    }
    
    public function sentOrReceived() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'sender=\''.user()->getId().'\' OR recipient=\''.user()->getId().'\'',
        ]);
        return $this;
    }

    public function sent() 
    {
        $this->resetScope();//this is to reset any prevoius scope such as mine() chained in front of this scope
        $this->getDbCriteria()->mergeWith([
            'condition'=>'sender = \''.user()->getId().'\'',
        ]);
        return $this;
    }
    
    public function read($id) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'id = '.$id,
        ]);
        return $this;
    }

    public function unread() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'receive_time is null',
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
        $criteria->compare('sender',$this->sender);
        $criteria->compare('recipient',$this->recipient,true);
        $criteria->compare('subject',$this->subject,true);
        $criteria->compare('content',$this->content,true);
        $criteria->compare('send_time',$this->send_time);
        $criteria->compare('receive_time',$this->receive_time);
        $criteria->compare('metadata',$this->metadata,true);

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
        return url('message/view/'.$this->id);
    }     
}