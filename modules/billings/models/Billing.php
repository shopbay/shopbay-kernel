<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.behaviors.*");
/**
 * This is the model class for table "s_billing".
 *
 * The followings are the available columns in table 's_billing':
 * @property integer $id
 * @property integer $account_id
 * @property string $customer_id
 * @property string $payment_method_token
 * @property integer $billing_day_of_month
 * @property string $billing_to
 * @property string $billing_email
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Billing extends SActiveRecord
{
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
        return Sii::t('sii','Billing|Billings',array($mode));
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_billing';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => [
              'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'account' => [
              'class'=>'common.components.behaviors.AccountBehavior',
            ], 
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'account',
            ],
        );
    } 
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, customer_id, payment_method_token, billing_day_of_month, email', 'required'),
            array('account_id', 'numerical', 'integerOnly'=>true),
            array('customer_id', 'length', 'max'=>20),
            array('billing_day_of_month', 'numerical', 'integerOnly'=>true, 'min'=>1, 'max'=>31),
            array('payment_method_token', 'length', 'max'=>25),
            array('email', 'email'),
            array('billed_to, email', 'length', 'max'=>100),
            // The following rule is used by search().
            array('id, account_id, , customer_id, payment_method_token, billing_day_of_month, email, billed_to, create_time, update_time', 'safe', 'on'=>'search'),
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
            'customer_id' => Sii::t('sii','Customer ID'),
            'payment_method_token' => Sii::t('sii','Payment Method Token'),
            'billing_day_of_month' => Sii::t('sii','Billing Day of Month'),
            'email' => Sii::t('sii','Email'),
            'billed_to' => Sii::t('sii','Billed To'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }     
    /**
     * @return string Required by activityBehavior
     */
    public function getName()
    {
        return $this->displayName();
    }
    /**
     * @return string short name for payment method token
     */
    public function getToken()
    {
        return $this->payment_method_token;
    }
    /**
     * @return masked credit card number
     */   
    public static function maskedCardNumber($cardType,$bin,$last4,$showLastNDigits=4)
    {
        if ($cardType=='American Express')
            $masked = substr('#*** ****** *****', 0, -$showLastNDigits) . $last4;
        else 
            $masked = substr('#*** **** **** ****', 0, -$showLastNDigits) . $last4;
        
        $masked = str_replace('#', $bin[0], $masked);//extract first digit of card bin
        
        return $cardType.' ('.$masked.')';        
    }

    public function getViewUrl() 
    {
        return url('billing/settings');
    }

}
