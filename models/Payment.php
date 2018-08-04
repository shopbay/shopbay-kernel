<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_payment".
 *
 * The followings are the available columns in table 's_payment':
 * @property integer $id
 * @property integer $account_id
 * @property string $payment_no
 * @property string $reference_no
 * @property string $type
 * @property integer $payment_method
 * @property string $currency
 * @property string $amount
 * @property string $trace_no
 * @property integer $create_time
 *
 * @author kwlok
 */
class Payment extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Payment the static model class
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
        return Sii::t('sii','Payment|Payments',array($mode));
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_payment';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
              'class'=>'common.components.behaviors.TimestampBehavior',
              'updateEnable'=>false,
            ],
            'accountbehavior' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'account',
                'localeAttribute'=>'profileLocale',
            ],  
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],             
            'paymentformbehavior' => [
                'class'=>'common.modules.payments.behaviors.PaymentFormBehavior',
            ],                
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, payment_no, reference_no, type, payment_method, currency, amount', 'required'),
            array('type', 'numerical', 'integerOnly'=>true),
            array('payment_no, reference_no', 'length', 'max'=>20),
            array('account_id', 'length', 'max'=>12),
            array('currency', 'length', 'max'=>3),
            array('amount', 'length', 'max'=>10),
            array('payment_method', 'length', 'max'=>1500),
            array('trace_no', 'length', 'max'=>500),
            
            array('id, account_id, reference_no, payment_no, type, payment_method, currency, amount, trace_no, create_time', 'safe', 'on'=>'search'),
        );
    }

    public function paymentType($type) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'type='.$type,
        ));
        return $this;
    }
    
    public function paymentNo($paymentNo) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'payment_no=\''.$paymentNo.'\'',
        ));
        return $this;
    }

    public function referenceNo($referenceNo) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'reference_no=\''.$referenceNo.'\'',
        ));
        return $this;
    }    
    
    public function invoice($type,$reference) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'type='.$type.' AND reference_no=\''.$reference.'\'',
        ));
        return $this;
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account ID'),
            'payment_no' => Sii::t('sii','Payment No'),
            'type' => Sii::t('sii','Type'),
            'payment_method' => Sii::t('sii','Payment Method'),
            'currency' => Sii::t('sii','Currency'),
            'amount' => Sii::t('sii','Amount'),
            'reference_no' => Sii::t('sii','Reference No'),
            'trace_no' => Sii::t('sii','System Trace No'),
            'create_time' => Sii::t('sii','Payment Date'),
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
        $criteria->compare('payment_no',$this->payment_no,true);
        $criteria->compare('type',$this->type);
        $criteria->compare('payment_method',$this->payment_method);
        $criteria->compare('currency',$this->currency,true);
        $criteria->compare('amount',$this->amount,true);
        $criteria->compare('reference_no',$this->reference_no,true);
        $criteria->compare('trace_no',$this->trace_no,true);
        $criteria->compare('create_time',$this->create_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }

    public function getTraceNo($returnAsArray=false)
    {
        if ($returnAsArray)
            return json_decode($this->trace_no,true);
        
        $form = PaymentMethod::getFormInstance($this->getPaymentMethodMode());
        $result = $form->parseTraceNo($this->trace_no);
        if ($result!=false)
            return $result;
        else {
            $trace = json_decode($this->trace_no);
            if (is_object($trace)){//MESSAGE object exists (by transition)
                $message = $trace->{Transition::MESSAGE};
                if (is_object($message))//embedded object inside MESSAGE
                    return Helper::htmlSmartKeyValues($message);
                else {
                    return $message;
                }
            }
            else 
                return $this->trace_no;
        }
    }

    const SALE      = 0;
    const REFUND    = 1;
    const SUBSCRIPTION = 2;
    const VOID = 3;

    public function getTypes()
    {
        return array(
            self::SALE => 'SALE',
            self::REFUND => 'REFUND',
            self::SUBSCRIPTION => 'SUBSCRIPTION',
            self::VOID => 'VOID',
            //other types yet to be supported...
        );
    }        

    public function getTypeDesc()
    {
        $types = $this->getTypes();
        return $types[$this->type];
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('payment/view/'.$this->payment_no);
    }
}