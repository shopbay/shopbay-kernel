<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.models.AddressTrait");
/**
 * This is the model class for table "s_order_address".
 *
 * The followings are the available columns in table 's_order_address':
 * @property integer $id
 * @property integer $order_id
 * @property string $recipient
 * @property string $mobile
 * @property string $address1
 * @property string $address2
 * @property string $postcode
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $email
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Order $order
 *
 * @author kwlok
 */
class OrderAddress extends CActiveRecord
{
    use AddressTrait;
    /**
     * Returns the static model of the specified AR class.
     * @return OrderAddress the static model class
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
        return 's_order_address';
    }

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
            //scenario: Shipping
            ['address1, postcode, city, state, country', 'required','on'=>'shipping'],
            ['id, order_id, recipient, mobile, address1, address2, postcode, city, state, country, create_time, update_time', 'safe', 'on'=>'shipping'],

            ['recipient', 'required'],
            ['order_id, mobile', 'numerical', 'integerOnly'=>true],
            ['recipient', 'length', 'max'=>32],
            ['mobile, postcode', 'length', 'max'=>20],
            ['address1, address2', 'length', 'max'=>100],
            ['state, city, country', 'length', 'max'=>40],
            ['email', 'length', 'max'=>200],
            ['email', 'email'],
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            ['id, order_id, recipient, mobile, address1, address2, postcode, city, state, country, email, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'order' => [self::BELONGS_TO, 'Order', 'order_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'order_id' => Sii::t('sii','Order'),
            'recipient' => Sii::t('sii','Recipient'),
            'mobile' => Sii::t('sii','Mobile'),
            'address1' => Sii::t('sii','Address'),
            'address2' => Sii::t('sii','Address2'),
            'postcode' => Sii::t('sii','Postcode'),
            'city' => Sii::t('sii','City'),
            'state' => Sii::t('sii','State'),
            'country' => Sii::t('sii','Country'),
            'email' => Sii::t('sii','Email Address'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('order_id',$this->order_id);
        $criteria->compare('recipient',$this->recipient,true);
        $criteria->compare('mobile',$this->mobile,true);
        $criteria->compare('address1',$this->address1,true);
        $criteria->compare('address2',$this->address2,true);
        $criteria->compare('postcode',$this->postcode,true);
        $criteria->compare('city',$this->city,true);
        $criteria->compare('state',$this->state,true);
        $criteria->compare('country',$this->country,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }    
    /**
     * Filtered by email
     * @param type $email
     * @return \OrderAddress
     */
    public function byEmail($email) 
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('email = \''.$email.'\'');
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }       
      
}