<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_wizard".
 *
 * The followings are the available columns in table 's_wizard':
 * @property integer $id
 * @property integer $caller
 * @property integer $profile
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Wizard extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Zone the static model class
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
        return Sii::t('sii','Wizard|Wizards',[$mode]);
    }   
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_wizard';
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
            ['caller, profile, status', 'required'],
            ['caller', 'length', 'max'=>100],
            ['profile', 'length', 'max'=>200],
            ['status', 'length', 'max'=>20],
            ['id, caller, profile, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'caller' => Sii::t('sii','Caller'),
            'profile' => Sii::t('sii','Profile'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Creation Date'),
            'update_time' => Sii::t('sii','Update Date'),
        ];
    }
    /**
     * File all by caller
     * @param type $id
     * @return \Wizard
     */
    public function caller($caller,$status=null) 
    {
        if (!isset($status))
            $status = Process::YES;
        
        $this->getDbCriteria()->mergeWith([
            'condition'=>'caller=\''.$caller.'\' AND status=\''.$status.'\'',
        ]);
        
        return $this;
    }    
    /**
     * Get profile by caller
     * @param type $id
     * @return \Wizard
     */
    public function profile($caller,$profile,$status=null) 
    {
        if (!isset($status))
            $status = Process::YES;
        
        $this->getDbCriteria()->mergeWith([
            'condition'=>'caller=\''.$caller.'\' AND profile=\''.$profile.'\' AND status=\''.$status.'\'',
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
        $criteria->compare('caller',$this->country,true);
        $criteria->compare('profile',$this->state,true);
        $criteria->compare('status',$this->city,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider('Wizard',[
                            'criteria'=>$criteria,
                            'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                        ]);

        logTrace(__METHOD__,$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('wizard/view/'.$this->id);
    } 
}