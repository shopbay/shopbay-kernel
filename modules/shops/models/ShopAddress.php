<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.models.AddressTrait");
/**
 * This is the model class for table "s_shop_address".
 *
 * The followings are the available columns in table 's_shop_address':
 * @property integer $id
 * @property integer $shop_id
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
 * @property Shop $shop
 *
 * @author kwlok
 */
class ShopAddress extends SActiveRecord
{
    use AddressTrait;
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ShopAddress the static model class
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
        return 's_shop_address';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Shop Address|Shop Addresses',array($mode));
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
                'accountSource'=>'shop',
            ), 
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'shop',
                'descriptionAttribute'=>'longAddress',
            ),
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('shop_id, address1, postcode, city, country', 'required'),
            array('shop_id, postcode', 'numerical', 'integerOnly'=>true),
            array('postcode', 'length', 'max'=>20),
            array('address1, address2', 'length', 'max'=>100),
            array('city, state, country', 'length', 'max'=>40),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, shop_id, address1, address2, postcode, city, state, country, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'shop' => array(self::BELONGS_TO, 'Shop', 'shop_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'shop_id' => Sii::t('sii','Shop'),
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
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('shop_id',$this->shop_id);
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

    public function toArray()
    {
        return array(
            array('name'=>'address1','value'=>$this->shortAddress),
            array('name'=>'city','value'=>$this->city),
            array('name'=>'postcode','value'=>$this->postcode),
            array('name'=>'state','value'=>$this->state!=null?SLocale::getStates($this->country,$this->state):Sii::t('sii','unset')),
            array('name'=>'country','value'=>SLocale::getCountries($this->country)),
        );
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('shop/address/'.$this->shop->slug);
    }        
}
