<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_auth_assignment".
 *
 * The followings are the available columns in table 's_auth_assignment':
 * @property string $itemname
 * @property string $userid
 * @property string $bizrule
 * @property string $data
 * 
 * @author kwlok
 */
class AuthAssignment extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return AuthAssignment the static model class
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
        return 's_auth_assignment';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('itemname, userid', 'required'),
            array('itemname, userid', 'length', 'max'=>64),
            array('bizrule, data', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('itemname, userid, bizrule, data', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'itemname' => 'Itemname',
            'userid' => 'Userid',
            'bizrule' => 'Bizrule',
            'data' => 'Data',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $criteria=new CDbCriteria;

        $criteria->compare('itemname',$this->itemname,true);
        $criteria->compare('userid',$this->userid,true);
        $criteria->compare('bizrule',$this->bizrule,true);
        $criteria->compare('data',$this->data,true);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }

    public static function hasRole($userId,$role)
    {
        foreach (self::getRoles($userId) as $assignment) {
            if ($assignment->itemname==$role)
                return true;
        }
        return false;
    } 
    
    public static function getRoles($userId=null)
    {
        $criteria=new CDbCriteria;
        $criteria->select='distinct itemname';
        if (isset($userId) && $userId!=Account::SUPERUSER)
            $criteria->condition='userid=\''.$userId.'\'';
        return AuthAssignment::model()->findAll($criteria);
    } 

    public static function getUsers($role=null){
        $criteria=new CDbCriteria;
        $criteria->select='userid';
        if (isset($role))
            $criteria->condition='itemname=\''.$role.'\'';
        return AuthAssignment::model()->findAll($criteria);
    } 
        
}