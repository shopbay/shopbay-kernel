<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.notifications.models.Notification");
/**
 * This is the model class for table "s_message_queue".
 *
 * The followings are the available columns in table 's_message_queue':
 * @property integer $id
 * @property integer $type Refer to s_notification.type
 * @property string $message
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 * 
 * @author kwlok
 */
class MessageQueue extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return MessageQueue the static model class
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
        return 's_message_queue';
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
            ['type, message, status', 'required'],
            ['type', 'numerical', 'integerOnly'=>true],
            ['status', 'length', 'max'=>20],
            
            ['id, type, message, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'type' => Sii::t('sii','Type'),
            'message' => Sii::t('sii','Message'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),            
        ];
    }
    
    public function email() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'type='.Notification::$typeEmail,
        ]);
        return $this;
    } 
    
    public function waitlist($type=null) 
    {
        $condition = 'status!=\''.Process::OK.'\'';
        if (isset($type))
            $condition .= ' AND type='.$type;
        $this->getDbCriteria()->mergeWith([
            'condition'=>$condition,
        ]);
        return $this;
    }   
    
    public function purgelist($type) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'type='.$type.' AND status=\''.Process::OK.'\'',
        ]);
        return $this;
    } 
    
    public function processed() 
    {
        return $this->status == Process::OK;
    } 
    
    public function hold() 
    {
        return $this->status == Process::HOLD;
    }   
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('type',$this->type);
        $criteria->compare('message',$this->message,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);
        
        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    private $_data;
    public function getData()
    {
        if (!isset($this->_data)){
            $this->_data = json_decode($this->message);
        }
        return $this->_data;
    }

    public function getHtmlStatusTag()
    {
        if ($this->status==Process::OK)
            return ['text'=>Sii::t('sii','Sent'),'color'=>'green'];
        else
            return ['text'=>$this->status,'color'=>'orange'];
    }

    public function getViewUrl()
    {
        return url('notifications/emailTrail/view/'.$this->id);
    }

}
