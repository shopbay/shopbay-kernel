<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PaymentForm
 *
 * @author kwlok
 */
class PaymentForm extends CFormModel 
{
    public $id;//payment method id (if have, it will be the CActiveRecord PaymentMethod id
    public $payer;//the person who initiate the request
    public $recipient;//the person who receive the payment
    public $type;
    public $method;
    public $status;
    public $currency;
    public $amount;
    public $reference_no;//normally is order no
    /**
     * System trace no
     * E.g. paypal reference, stored in json_encode array form - contains 3 fields
     *  {"TIMESTAMP":"2013-09-11T17:29:51Z",
     *   "CORRELATIONID":"e89471ccb182f",
     *   "PAYMENTINFO_0_TRANSACTIONID":"0FC01485W1003964M"}
     * or, bank fund transfer reference no
     */
    public $trace_no;//
    /**
     * Payment note (user input)
     * E.g. Fund transfer remarks
     */
    public $note;
    public $verify_code;
    public $shop_id;//shop merchant
    public $extraPaymentData;//optional - can be PayPal response, braintree nonce etc
    public $paymentGatewayData;//optional - payment gateway information
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
            ),
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),
        );
    }    
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('method, payer, reference_no, type, status, currency, amount', 'required'),
            array('method', 'verifyMethod'),
            array('trace_no', 'verifyNotNull'),
            array('trace_no, note', 'safe'),
            // verifyCode needs to be entered correctly
            //array('verify_code', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),

            array('method, payer, reference_no, type, status, currency, amount', 'required','on'=>'refund'),
        );
    }
    /**
     * Verify if payment method is allowed
     */
    public function verifyMethod()
    {
       if (!in_array($this->id, $this->allowedMethods()))
            $this->addError('id',Sii::t('sii','Payment method not allowed'));
    }
    
    public function allowedMethods($locale=null)
    {
        $allowedMethod = new CList();
        foreach($this->getPaymentMethods() as $method) {
            $allowedMethod->add($method->id);
        }
        return $allowedMethod->toArray();
    }

    public function verifyNotNull()
    {
        if($this->method == PaymentMethod::PAYPAL_EXPRESS_CHECKOUT){
            if (empty($this->trace_no))
                $this->addError('trace_no',Sii::t('sii','System Trace No cannot be blank'));
            if (strlen($this->trace_no)>200)
                $this->addError('trace_no',Sii::t('sii','System Trace No maximum length 200 chars'));
        }
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'trace_no' => Sii::t('sii','Transaction Reference No'),
            'note' => Sii::t('sii','Fund Transfer Note'),
            'verify_code'=>Sii::t('sii','Verification Code'),
        );
    }
    
    public function getHasShop()
    {
        return $this->shop_id!=null;
    }
    /**
     * Used for LocaleBehavior
     * @return Shop
     */    
    public function getShop()
    {
        if (isset($this->shop_id))
            return Shop::model()->findByPk($this->shop_id);
        else
            return null;
    }
    
    public function getPaymentMethod($method)
    {
        return PaymentMethod::model()->shopAndMethod($this->shop_id,$method)->find();
    }
    /**
     * Return payment method name
     * @return string
     */    
    public function getMethodName($method)
    {
        if ($this->hasPaymentMethod())
            return $this->getPaymentMethodModel()->name;//return raw name with multi-lang
        else 
            return PaymentMethod::getName($method);
    }    
        
    private $_pm;
    public function getPaymentMethodModel()
    {
        if ($this->_pm===null){
            $model = PaymentMethod::model()->findByPk($this->id);
            if ($model!=null){
                $this->_pm = $model;
            }
        }
        return $this->_pm;
    }    
    public function hasPaymentMethod()
    {
        return $this->getPaymentMethodModel()!=null;
    }
    
    public function getPaymentMethods($excludeMethod=null)
    {
        if (isset($excludeMethod)){
            $criteria = new CDbCriteria();
            $criteria->condition = 't.method!='.$excludeMethod;
        }
        return $this->shop->searchPaymentMethods(Process::PAYMENT_METHOD_ONLINE,isset($criteria)?$criteria:null)->data;
    }
    
    public function getPaymentMethodData()
    {
        $data = [];
        if ($this->hasPaymentMethod()){
            //scan thru all supported locales at shop levels
            $name = new CMap();
            foreach ($this->shop->getLanguageKeys() as $language) {
                $name->add($language,$this->getPaymentMethodModel()->getMethodName($language));
            }
            //set initial data
            $data = [
                'id'=>$this->getPaymentMethodModel()->id,
                'modelClass'=>get_class($this->getPaymentMethodModel()),
                'name'=>$name->toArray(),
                'mode'=>$this->getPaymentMethodModel()->method,
                'offline'=>$this->getPaymentMethodModel()->isOfflineMethod()?true:false,
            ];
        }
        else {//no payment method record found; Expect to be internally used by system
            $data = [
                'name'=>$this->method==PaymentMethod::BRAINTREE_CREDITCARD ? 'Credit Card' : 'unset',
                'mode'=>$this->method,
            ];
        }
        return json_encode($data);
    }        
}