<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of OfflinePaymentForm
 *
 * @author kwlok
 */
class OfflinePaymentForm extends PaymentMethodPluginForm 
{
    protected $pluginName = 'offlinePayment';
    protected $viewFileName = '_form_offline';
    /*
     * Attributes to persists its value while doing validation; 
     * As validation is done attribute by attribute, 
     * Certain validation rules requires other attribute value to be presented requires this feature
     */
    protected $persistentAttributes = array('shop_id','name','instructions');
    protected $excludeAttributes = array('description');
    /*
     * Local attributes
     */
    public $sourceMethod = PaymentMethod::OFFLINE_PAYMENT;
    public $mode;//payment mode - should equal to parent method when stored into db
    public $instructions;
    /**
     * @return array of attributes required to support locales
     */
    public function localeAttributes() 
    {
        $attributes = array_merge(parent::localeAttributes(),array(
            'instructions'=>array(
                'required'=>true,
                'inputType'=>'textArea',
                'inputHtmlOptions'=>array('rows'=>10,'maxlength'=>500),
                'note'=>array(
                    OfflinePaymentForm::getNote1(),
                    OfflinePaymentForm::getNote2()
                ),
            ),
        ));
        foreach ($this->excludeAttributes as $value) {
            unset($attributes[$value]);//remove excluded attributes
        }
        return $attributes;
    }    
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('mode, sourceMethod, instructions', 'required'),
            array('method, mode', 'numerical', 'integerOnly'=>true),
            array('mode','ruleModeUnique','on'=>'create'),
            array('instructions', 'length', 'max'=>500),
            array('instructions', 'rulePurify'),
        ));
    } 
    /**
     * Payment mode uniqueness check
     */
    public function ruleModeUnique($attribute,$params)
    {
        if ($this->mode!=PaymentMethod::OTHERS){
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(array('shop_id'=>$this->shop_id));
            $criteria->compare('params','"mode":"'.$this->mode.'"',true,'AND',true);
            logTrace(__METHOD__,$criteria);
            $count = PaymentMethod::model()->count($criteria);
            if ($count>=1)                
                $this->addError('mode',Sii::t('sii','Offline payment method {name} already exists.',array('{name}'=>PaymentMethod::getOfflineName($this->mode))));
        }
    }     
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'instructions' => Sii::t('sii','Payment Instructions'),
            'mode' => Sii::t('sii','Payment Mode'),
        ));
    }
    
    public function getAvailableModes()
    {
        $names = PaymentMethod::getOfflineNames();
        unset($names[PaymentMethod::UNDEFINED]);
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('shop_id'=>$this->shop_id));
        $methods = PaymentMethod::model()->findAll($criteria);
        foreach ($methods as $method) {
            if ($method->method!=PaymentMethod::OTHERS)
                if (array_key_exists($method->method, $names))
                    unset($names[$method->method]);
        }
        return $names;
    }     
    /**
     * Instructions template
     * @param type $method
     * @param type $locale
     * @return type
     */
    public static function getInstructions($method,$locale=null)
    {
        switch ($method) {
            case PaymentMethod::ATM_CASH_BANK_IN:
                return  Sii::t('sii','Please make your payment to confirm your order:{newline}',array('{newline}'=>PHP_EOL),null,$locale)
                       .Sii::t('sii','{newline}Bank Name: <your bank name>{newline}Account Number: <your bank account number>{newline}Account Name: <your bank account name>{newline}',array('{newline}'=>PHP_EOL),null,$locale)
                       .Sii::t('sii','{newline}Please update us when your payment is done by setting order to "{status}". Upon successful payment verification, we will start process your order.',array('{newline}'=>PHP_EOL,'{status}'=>Process::getDisplayText(Process::getText(Process::PAID),$locale)),null,$locale);
            case PaymentMethod::CASH_ON_DELIVERY:
                return Sii::t('sii','Upon delivering order, our shipping guy will collect order amount from you, payable by cash or cheque.',array(),null,$locale);
            default:
                return Sii::t('sii','Please enter your payment instructions here',array(),null,$locale);
        }
    }
    
    public function renderViewInCart($cartPaymentMethodForm)
    {
        if ($this->method==PaymentMethod::ATM_CASH_BANK_IN)
            return Yii::app()->controller->renderPartial($this->getViewFileBaseAlias().'.'.'_cart_atmcash',array('model'=>$cartPaymentMethodForm),true);
        if ($this->method==PaymentMethod::CASH_ON_DELIVERY)
            return Yii::app()->controller->renderPartial($this->getViewFileBaseAlias().'.'.'_cart_cod',array('model'=>$cartPaymentMethodForm),true);
        if ($this->method==PaymentMethod::OTHERS)
            return Yii::app()->controller->renderPartial($this->getViewFileBaseAlias().'.'.'_cart_others',array('model'=>$cartPaymentMethodForm),true);
    }
    /**
     * Define the payment method selection onClick behavior (at shopping cart)
     * @see /modules/carts/actions/ButtonGetAction
     * @param array $params Must contain following parameters
     * Format: array(
     *     'buttonName' => ...,
     *     'methodId' => ...,
     *     'paymentMethod' => ...,
     *     'shopId' => ...,
     *     'formId' => ...,
     *     'amount' => ...,
     * )
     */
    public function onMethodSelected($params) 
    {
        return array(
            'buttonName'=>$params['buttonName'],
            'buttonMethod'=>$params['paymentMethod'],
            'buttonDisable'=>false,
            'buttonClick'=> 'SelectPaymentMethod',
            'callback'=> false,
        );
    }
    /**
     * Trace no parsing method; Apply to external plugin such as PayPal, Braintree etc
     * @see Model Payment->trace_no
     * @param $trace raw trace stored in Payment model record
     * @return mixed False if not parsing required; Else, will return parsing result.
     */
    public function parseTraceNo($trace) 
    {
        return false;
    }     
    
}