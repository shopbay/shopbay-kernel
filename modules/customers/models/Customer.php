<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
Yii::import('common.modules.customers.models.CustomerData');
Yii::import('common.modules.customers.models.CustomerAddressData');
/**
 * This is the model class for table "s_customer".
 *
 * The followings are the available columns in table 's_customer':
 * @property integer $id
 * @property integer $account_id
 * @property string $customer_id 
 * @property string $first_name 
 * @property string $last_name 
 * @property string $alias_name 
 * @property string $gender
 * @property string $birthday
 * @property string $locale
 * @property integer $image
 * @property string $address
 * @property string $data Refer to CustomerData
 * @property string $notes
 * @property string $tags
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class Customer extends SActiveRecord
{
    private $_d;//data holder
    private $_a;//address holder
    private $_o;//last order holder
    private $_s;//last visited shop
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shipping the static model class
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
        return Sii::t('sii','Customer|Customers',[$mode]);
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_customer';
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
            //-- Begin Notes
            //This account related behaviors only works for Registered Account; 
            //Guest account will not work since value of attribute customer_id is email address
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountAttribute'=>'customer_id',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'customer_id',
            ],
            //-- End Notes
            'image' => [
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'transitionMedia'=>false,
                'label'=>Sii::t('sii','Avatar'),
                'stateVariable'=>SActiveSession::ACCOUNT_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_ACCOUNT,
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'self',
                'localeAttribute'=>'locale',
            ],
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'alias_name',
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
            ['account_id', 'required'],
            ['account_id', 'numerical', 'integerOnly'=>true],
            //We will keep guest buyer's email as customer_id, hence need to cater enough length to store email address.
            ['customer_id', 'length', 'max'=>120],
            ['alias_name, first_name, last_name', 'length', 'max'=>50],
            ['address, tags', 'length', 'max'=>500],
            ['data, notes', 'length', 'max'=>1000],
            ['gender', 'length', 'max'=>1],
            ['birthday, locale', 'safe'],
            ['birthday', 'default', 'setOnEmpty'=>true, 'value' => null],
            ['image', 'numerical', 'integerOnly'=>true],
            ['address', 'ruleAddress'],

            ['alias_name', 'required','on'=>'create'],
            
            // The following rule is used by search().
            ['id, account_id, customer_id, alias_name, address, data, notes, tags, first_name, last_name, gender, birthday, mobile, image, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'merchant' => [self::BELONGS_TO, 'Account', 'account_id'],
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
            'customer_id' => Sii::t('sii','Customer ID'),
            'alias_name' => Sii::t('sii','Alias Name'),
            'first_name' => Sii::t('sii','First Name'),
            'last_name' => Sii::t('sii','Last Name'),
            'gender' => Sii::t('sii','Gender'),
            'birthday' => Sii::t('sii','Birthday'),
            'mobile' => Sii::t('sii','Mobile'),
            'locale' => Sii::t('sii','Language'),
            'image' => 'Avatar',
            'address' => Sii::t('sii','Address'),
            'data' => Sii::t('sii','Customer Data'),
            'tags' => Sii::t('sii','Tags'),
            'notes' => Sii::t('sii','Notes'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
            //attribute defined in CustomerData
            'last_shop' => Sii::t('sii','Last Visited Shop'),
            'last_order' => Sii::t('sii','Last Order'),
            'orders' => Sii::t('sii','Recent Orders'),
            'shops' => Sii::t('sii','Visited Shops'),
            //attribute defined in CustomerShopData
            'total_orders' => Sii::t('sii','Total Orders'),
            'total_spent' => Sii::t('sii','Total Spent'),
            //attribute defined in CustomerAddressData
            'location' => Sii::t('sii','Location'),
            //attribute defined in CustomerAccount
            'registered' => Sii::t('sii','Registered Account'),
            'last_login_time' => Sii::t('sii','Last Login Time'),
            'email' => Sii::t('sii','Email'),
        ];
    }
    
    public function ruleAddress($attribute,$params)
    {
        if (!empty($this->address)){
            $addressForm = new CustomerAddressForm();
            $addressForm->fillForm($this->getAddressData());
            if (!$addressForm->validate()){
                $this->addError('address', Helper::htmlErrors($addressForm->errors));
            }
        }
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return [
            'tags' => Sii::t('sii','You can enter multiple tags using comma as separator. For example, "prospects, VIP" etc.'),
            'notes' => Sii::t('sii','You can enter any extra information you want to keep track for this customer.'),
        ];
    }  

    public function merchantAccount($id) 
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('account_id = '.$id);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }    
    
    public function retrieveRecord($merchant,$customer) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['account_id'=>$merchant]);
        $criteria->addColumnCondition(['customer_id'=>$customer]);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * Update customer for certain fields input by $merchant only
     * @param type $merchant
     * @return type
     */
    public function updatable($merchant)
    {
        return $this->account_id==$merchant;
    }
    /**
     * Customer lead created by merchant can be updated
     * @param type $merchant
     * @return type
     */
    public function addressUpdatable()
    {
        return !$this->isRegistered;
    }
    /**
     * Customer lead created by merchant can be updated
     * @param type $merchant
     * @return type
     */
    public function profileUpdatable()
    {
        return !$this->isRegistered;
    }
    /**
     * Registered customer cannot be deleted
     * Customer lead created by merchant can be deleted
     * @param type $merchant
     * @return type
     */
    public function deletable($merchant)
    {
        return $this->account_id==$merchant && !$this->isRegistered;
    }    
    /**
     * Return customer data object
     */
    public function getCustomerData()
    {
        if ($this->_d===null){
            $data = new CustomerData();
            $data->assignData($this->data);
            $this->_d = $data;
        }
        return $this->_d;
    }
    /**
     * Check if customer has data 
     */
    public function hasCustomerData()
    {
        return $this->customerData->hasData();
    }   
    /**
     * Return customer address used by this customer
     */
    public function getAddressData()
    {
        if ($this->_a===null){
            $address = json_decode($this->address,true);
            $this->_a = new CustomerAddressData($address['address1'],$address['address2'],$address['postcode'],$address['city'],$address['state'],$address['country'],$address['mobile']);
        }
        return $this->_a;
    }   
    /**
     * Here will overwrite address data if existing already have one
     * System now does not implement address data history (should customer has use many different address)
     * 
     * @param CustomerAddressData $addressData
     * @throws CException
     */
    public function setAddressData($addressData)
    {
        if (!($addressData instanceof CustomerAddressData))
            throw new CException(Sii::t('sii','Invalid address data object'));
        
        $this->address = json_encode($addressData->toArray());//first or overwrite address data
    }    
    /**
     * Check if customer visited any shops
     */
    public function hasShops()
    {
        return $this->shopCount>0;
    }   
    /**
     * Return shop count visited by this customer
     */
    public function getShopCount()
    {
        return count($this->customerData->shop_data);
    }   
    /**
     * Return visited shops visited by this customer
     */
    public function getShopData()
    {
        $sd = new CList();
        foreach ($this->customerData->shop_data as $key => $value) {
            $data = new CustomerShopData($value['total_spent'],$value['total_orders'],$value['last_order_id']);
            $data->shop_id = $key;
            $sd->add($data);
        }
        return $sd->toArray();
    }   
    /**
     * Return total orders purchased by this customer
     */
    public function getTotalOrders()
    {
        $count = 0;
        foreach ($this->customerData->shop_data as $shop => $data) {
            $count += $data['total_orders'];
        }
        return $count;
    }       
    /**
     * Return last order purchased by this customer
     */
    public function getLastOrder()
    {
        if (isset($this->customerData->last_order_id)){
            if ($this->_o===null){
                $this->_o = Order::model()->findByPk($this->customerData->last_order_id);
            }
        }
        return $this->_o;
    }    
    /**
     * Return last order link purchased by this customer
     */
    public function getLastOrderLink()
    {
        return $this->lastOrder->orderPaid()?CHtml::link($this->lastOrder->order_no,$this->lastOrder->viewUrl):$this->lastOrder->order_no;
    }    
    /**
     * Return last shop visited by this customer
     */
    public function getLastShop()
    {
        if (isset($this->customerData->last_shop_id)){
            if ($this->_s===null){
                $this->_s = Shop::model()->findByPk($this->customerData->last_shop_id);
            }
        }
        return $this->_s;
    } 
    /**
     * Return last shop link purchased by this customer
     */
    public function getLastShopLink()
    {
        return CHtml::link($this->lastShop->displayLanguageValue('name',user()->getLocale()),$this->lastShop->viewUrl);
    }        
    /**
     * Return recent orders data provider (last 5 orders only)
     * @return \CActiveDataProvider
     */
    public function getRecentOrders()
    {
        if ($this->isRegistered){
            return new CActiveDataProvider(Order::model()->merchant()->mine($this->customer_id)->all(), [
                        'criteria'=>[
                            'order'=>'create_time DESC',
                        ],
                        'pagination'=>['pageSize'=>5],
                        'sort'=>false,
                    ]);
            
        }
        else {
            $criteria = new CDbCriteria();
            $criteria->select = 't.*'; 
            $criteria->join = 'INNER JOIN '.OrderAddress::model()->tableName().' s ON t.id = s.order_id';
            $criteria->condition = 's.email= \''.$this->customer_id.'\'';//matching email address
            $criteria->order = 't.create_time DESC';
            return new CActiveDataProvider(Order::model()->merchant()->mine(Account::GUEST)->all(), [
                        'criteria'=> $criteria,
                        'pagination'=>['pageSize'=>5],
                        'sort'=>false,
                    ]);
            
        }
    }        
    /**
     * Return tags in array
     */
    public function hasTags()
    {
        return $this->tags!=null;
    }    
    /**
     * Return tags in array
     */
    public function parseTags()
    {
        logTrace(__METHOD__,$this->tags);
        return explode(',', $this->tags);
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        //$criteria->compare('id',$this->id);
        //$criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('customer_id',$this->customer_id);
        $criteria->compare('alias_name',$this->alias_name,true);
        $criteria->compare('first_name',$this->first_name,true);
        $criteria->compare('last_name',$this->last_name,true);
        $criteria->compare('gender',$this->gender,true);
        $criteria->compare('birthday',$this->birthday,true);
        $criteria->compare('mobile',$this->mobile,true);
        $criteria->compare('locale',$this->locale,true);
        $criteria->compare('image',$this->image);
        $criteria->compare('address',$this->address,true);
        $criteria->compare('data',$this->data,true);
        $criteria->compare('notes',$this->notes,true);
        $criteria->compare('tags',$this->tags,true);
        //$criteria->compare('create_time',$this->create_time);
        //$criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        $dataprovider = new CActiveDataProvider('Customer',[
                                'criteria'=>$criteria,
                                'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                            ]);

        logTrace(__METHOD__.' criteria',$dataprovider->criteria);

        return $dataprovider;
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        if (app()->id=='shop')
            return url('account/profile');
        else
            return url('customer/view/'.$this->id);
    } 
    /**
     * Registered (sign up) customer will be assigned id start with Account::TYPE_CUSTOMER and one running number
     * e.g. c1, c2 ... cN
     * Guest account will be assigned email address as their customer id
     * 
     * @return boolean
     */
    public function getIsRegistered()
    {
        $customerId = $this->customer_id;
        return $this->account!=null && 
               $customerId!=null && 
               substr($customerId,0,1) == Account::TYPE_CUSTOMER &&
               !(strpos($customerId, '@') !== false);//not containing email char @
    }

    public function getRegisteredTag()
    {
        return Helper::htmlColorText($this->getRegisteredText(),true,true);
    }
    
    public function getRegisteredText()
    {
        return ['text'=>Sii::t('sii','Registered Account'),'color'=>'skyblue'];
    }
    /**
     * Get alias 
     * @return type
     */
    public function getAlias()
    {
        if ($this->alias_name!=null)
            return $this->alias_name;
        else
            return Sii::t('sii','New Customer'); 
    }    
    /**
     * Get email address 
     * @return type
     */
    public function getEmail()
    {
        return $this->isRegistered ? $this->account->email: $this->customer_id;
    }    
    
}