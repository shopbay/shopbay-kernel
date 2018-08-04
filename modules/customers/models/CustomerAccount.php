<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.accounts.models.BaseAccount");
Yii::import("common.components.validators.CompositeUniqueKeyValidator");
/**
 * This is the model class for table "s_customer_account".
 *
 * The followings are the available columns in table 's_customer_account':
 * @property integer $id
 * @property integer $shop_id
 * @property string $email
 * @property string $password
 * @property integer $status
 * @property string $reg_ip
 * @property string $activate_str
 * @property integer $activate_time
 * @property string $last_login_ip
 * @property integer $last_login_time
 * @property integer $create_time
 * @property integer $update_time
 * 
 * @author kwlok
 */
class CustomerAccount extends BaseAccount
{
    private $_p;//profile instance
    /**
     * Returns the static model of the specified AR class.
     * @return Account the static model class
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
        return 's_customer_account';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //override setting
        $behaviors['activity'] = [
            'class'=>'common.modules.activities.behaviors.ActivityBehavior',
            'descriptionAttribute'=>'nickname',
            'iconUrlSource'=>'profile',
        ];
        return $behaviors;
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['shop_id, password, email, status', 'required'],
            ['email', 'email'],
            ['email', 'CompositeUniqueKeyValidator','keyColumns'=>'shop_id, email','errorMessage'=>Sii::t('sii','Email is already taken'),'on'=>'create'],
            ['password', 'length', 'max'=>64],//hashed password - standard 64 chars
            ['email', 'length', 'max'=>100],
            ['status', 'safe'],
            ['status', 'length', 'max'=>20],
            ['reg_ip', 'length', 'max'=>15],
            ['activate_str', 'length', 'max'=>50],
            
            ['id, shop_id, email, password, status, reg_ip, activate_str, activate_time, last_login_ip, last_login_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
        ];
    }
    /**
     * Get profile object
     */
    public function getProfile()
    {
        if (!isset($this->_p))
            $this->_p = Customer::model()->find('customer_id=\''.Account::encodeId(Account::TYPE_CUSTOMER, $this->id).'\'');
        return $this->_p;
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'shop_id' => Sii::t('sii','Shop'),
            'email' => Sii::t('sii','Email'),
            'password' => Sii::t('sii','Password'),
            'status' => Sii::t('sii','Status'),
            'reg_ip' => 'Reg Ip',
            'activate_str' => 'Activate Str',
            'activate_time' => 'Activate Time',
            'last_login_ip' => Sii::t('sii','Last Login IP'),
            'last_login_time' => Sii::t('sii','Last Login Time'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    /**
     * Get the shop name (for notification use case)
     * @param type $locale
     * @return type
     */
    public function getShopName($locale=null)
    {
        return $this->shop->displayLanguageValue('name',$locale);
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('password',$this->password,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('reg_ip',$this->reg_ip,true);
        $criteria->compare('activate_str',$this->activate_str,true);
        $criteria->compare('activate_time',$this->activate_time);
        $criteria->compare('last_login_ip',$this->last_login_ip,true);
        $criteria->compare('last_login_time',$this->last_login_time);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }
    /**
     * Change email address
     * @param array $params Contains "email", and "userid"
     */
    public function changeEmail($params)
    {
        if (isset($params['email']) && isset($params['userid'])){
            $this->email = $params['email'];
            $this->prepareEmailActivation($params['userid']);
        }
        else
            throw new CException(Sii::t('sii','Missing params to change email'));
    }
    /**
     * Register account
     * 
     * Rules:
     * [1] Default assign Role User and Customer (buyer)
     * [2] Default create an profile with locale = en_sg, if profile is passing in, it will populate profile data
     * 
     * General rule of thumb for permission assignments:
     * All account is a buyer (customer), but not necessary is a seller (merchant)
     * If an account is signed up as seller (merchant), he/she will always be a buyer too
     * 
     * @param Customer::address
     */
    public function register($addressData=[])
    {
        $this->insert();   
        $this->refresh();//to get the new assigned id
        Yii::import("common.modules.accounts.users.IdentityCustomer");
        //[1] Each customer account has two roles assigned
        Account::assignSubAccountRole(Role::USER, Account::TYPE_CUSTOMER, $this->id);
        Account::assignSubAccountRole(Role::CUSTOMER, Account::TYPE_CUSTOMER, $this->id);
        
        //[2] Try to search if this is a converted guest account (guest customer id is the email address)
        $newSignup = false;
        $customer = Customer::model()->retrieveRecord($this->shop->account_id,$this->email)->find();
        if ($customer===null){ // new sign up
            $customer = new Customer();
            $newSignup = false;
        }
        $customer->customer_id = Account::encodeId(Account::TYPE_CUSTOMER, $this->id);//change customer_id to internal account id (no more email address)
        $customer->account_id = $this->shop->account_id;//to get merchant account
        $customer->locale = param('LOCALE_DEFAULT');//use default locale for initial setup
        if (!empty($addressData)){
            $address = new CustomerAddressData();
            foreach ($addressData as $key => $value) {
                $address->$key = $value;
            }
            $customer->address = $address->toString();
            logInfo(__METHOD__.' customer address created',$address->toArray());
        }
        
        if ($newSignup){
            $customer->insert();
            logInfo(__METHOD__.' New customer account registered.',$customer->getAttributes());
        }
        else {
            $customer->save();
            logInfo(__METHOD__." Guest account converted to new customer account.",$customer->getAttributes());
        }
    } 
    /**
     * Return account name (identical to email address)
     * To get profile name:
     * @see self::getNickname()
     * @return type
     */
    public function getName()
    {
        $this->email;
    }    
    /**
     * Return account nick name
     * @return type
     */
    public function getNickname()
    {
        if ($this->profile===null)
            return Sii::t('sii','Customer'); 
        else
            return $this->profile->alias;
    }    

    public function getViewUrl() 
    {
        return url('account');
    }
    /**
     * Get customer id (with prefix 'c') (unique user id across platform)
     * @inheritdoc
     */
    public function getUid()
    {
        return static::encodeId(static::TYPE_CUSTOMER, $this->id);
    }     
    /**
     * Transfer orders from converted guest account (soley based on email address)
     * @return 
     */
    public function transferOrders()
    {
        $customerId = Account::encodeId(Account::TYPE_CUSTOMER, $this->id);
        foreach ( OrderAddress::model()->byEmail($this->email)->findAll() as $record) 
        {
            //[1] Change order account_id to internal customer id (no more Account::GUEST)
            $record->order->account_id = $customerId;
            $record->order->save();
            logTrace(__METHOD__.' Change Order '.$record->order->order_no.' account_id to customer_id '.$customerId.' ... ok.',$record->order->getAttributes());
            //[2] Change item account_id to internal customer id (no more Account::GUEST)
            foreach ($record->order->items as $item) {
                $item->account_id = $customerId;
                $item->save();
                logTrace(__METHOD__.' Change Order item '.$item->id.' account_id to customer_id '.$customerId.' ... ok.',$item->getAttributes());
            }
            //[3] Change order address email to null
            $record->email = null;
            $record->save();
            logTrace(__METHOD__.' Change Order '.$record->order->order_no.' address email to null (for non-guest account) ... ok.');

            logInfo(__METHOD__.' Transfer Order '.$record->order->order_no.' to customer_id '.$customerId.' successfully.');
        }
    }
}