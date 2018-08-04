<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.models.AddressTrait");
/**
 * This is the model class for table "s_account_address".
 *
 * The followings are the available columns in table 's_account_address':
 * @property integer $id
 * @property integer $account_id
 * @property string $address1
 * @property string $address2
 * @property string $postcode
 * @property string $city
 * @property string $state
 * @property string $country
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 * 
 * @author kwlok
 */
class AccountAddress extends CActiveRecord
{
    use AddressTrait;
    /**
     * Returns the static model of the specified AR class.
     * @return AccountAddress the static model class
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
        return 's_account_address';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => array(
                'class'=>'common.components.behaviors.TimestampBehavior',
            ),
            'account' => array(
                'class'=>'common.components.behaviors.AccountBehavior',
            ),
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('address1, postcode, city, country', 'required'),
            array('address1, address2', 'length', 'max'=>100),
            array('postcode', 'length', 'max'=>20),
            array('state, city, country', 'length', 'max'=>40),
            
            array('id, account_id, address1, address2, postcode, city, state, country, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'address1' => Sii::t('sii','Address'),
            'address2' => Sii::t('sii','Address'),
            'postcode' => Sii::t('sii','Postal Code'),
            'city' => Sii::t('sii','City'),
            'state' => Sii::t('sii','State'),
            'country' => Sii::t('sii','Country'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('address1',$this->address1,true);
        $criteria->compare('address2',$this->address2,true);
        $criteria->compare('postcode',$this->postcode,true);
        $criteria->compare('city',$this->city,true);
        $criteria->compare('state',$this->state,true);
        $criteria->compare('country',$this->country,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }
 
}