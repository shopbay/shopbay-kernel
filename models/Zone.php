<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shippings.behaviors.ShippingBaseBehavior");
/**
 * This is the model class for table "s_zone".
 *
 * The followings are the available columns in table 's_zone':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $name
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $postcode
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class Zone extends SActiveRecord
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
        return Sii::t('sii','Zone|Zones',[$mode]);
    }   
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_zone';
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
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'includeGlobal'=>true,
            ],
            'merchant' => [
              'class'=>'common.components.behaviors.MerchantBehavior',
            ],  
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>true,
                //'iconUrlSource'=>'shop',
            ],
            'locale' => [
              'class'=>'common.components.behaviors.LocaleBehavior',
            ],     
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],   
            'shippingbehavior' => [
                'class'=>'ShippingBaseBehavior',
            ],              
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id, name, country', 'required'],
            ['account_id, shop_id', 'numerical', 'integerOnly'=>true],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            ['name', 'length', 'max'=>2000],
            ['state, city', 'length', 'max'=>100],
            ['country', 'length', 'max'=>200],
            ['postcode', 'length', 'max'=>20],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'],

            ['id, account_id, shop_id, name, country, state, city, postcode, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Validate if zone has any associations
     * (1) Attached to any shippings
     */
    public function ruleAssociations($attribute,$params)
    {
        if (Shipping::model()->all()->exists('zone_id='.$this->id))
            $this->addError('id',Sii::t('sii','"{object_name}" has associations with {association_object}. Please clear the association if you wish to delete this {object_type}.',
                    ['{object_name}'=>$this->localeName(user()->getLocale()),
                     '{association_object}'=>strtolower(Shipping::model()->displayName(Helper::PLURAL)),
                     '{object_type}'=> strtolower($this->displayName())]
            ));
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'shippings' => [self::HAS_MANY, 'Shipping', 'zone_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'name' => Sii::t('sii','Name'),
            'country' => Sii::t('sii','Country'),
            'state' => Sii::t('sii','State'),
            'city' => Sii::t('sii','City'),
            'postcode' => Sii::t('sii','Post Code'),
            'create_time' => Sii::t('sii','Creation Date'),
            'update_time' => Sii::t('sii','Update Date'),
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
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('country',$this->country,true);
        $criteria->compare('state',$this->state,true);
        $criteria->compare('city',$this->city,true);
        $criteria->compare('postcode',$this->postcode,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider('Zone',[
                            'criteria'=>$criteria,
                            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                        ]);

        logTrace(__METHOD__,$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchShippings()
    {
        return new CActiveDataProvider(Shipping::model()->all(),[//use all() to avoid selecting 'soft-deleted' records
                   'criteria'=>[
                        'condition'=>'zone_id='.$this->id,
                        'order'=>'create_time DESC',
                    ],
                    'pagination'=>[
                        'pageSize'=>Config::getSystemSetting('record_per_page'),
                    ],
                    'sort'=>false,                                        
                ]);
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('shipping/zone/view/'.$this->id);
    } 
    /**
     * Validate shipping address to check if the shipping zone is supported
     * It check value to value between the shipping address filled in the form against the Zone values
     * It may make sense to check address data elements that have fixed value such as country, postcode, city, state
     * and not fuzzy such as street.
     * 
     * @param mixed $shippingAddress the shpping address to be validated
     * @param Shipping $shippingModel the shpping model used to do validation
     * @param array $fields Default it checks against country (refer to SLocale countries dropdown list)
     * @return array of errors
     */
    public static function validateShippingAddress($shippingAddress, $shippingModel, $fields=['country'],$locale)
    {
        $error = new CMap();
        foreach ($fields as $field) {
            logTrace(__METHOD__.' input '.$field.':'.$shippingAddress->$field.', shipping value: '.$shippingModel->zone->$field);
            if ($shippingAddress->$field!=$shippingModel->zone->$field)
                $error->add($shippingModel->id,Sii::t('sii','Shipping "{shipping}" does not ship to {zone}',['{shipping}'=>$shippingModel->displayLanguageValue('name',$locale),'{zone}'=>SLocale::getCountries($shippingAddress->$field)]));
        }
        return $error->toArray();
    }
     
}