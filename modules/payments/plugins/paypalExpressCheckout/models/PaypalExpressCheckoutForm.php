<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PaypalExpressCheckoutForm
 *
 * @author kwlok
 */
class PaypalExpressCheckoutForm extends PaymentMethodPluginForm 
{
    protected $pluginName = 'paypalExpressCheckout';
    protected $viewFileName = '_form_paypal_express';
    /*
     * Local attributes
     */    
    public $email;
    public $apiUsername;
    public $apiPassword;
    public $apiSignature;
    public $apiMode = 0;//sandbox mode
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('email, apiUsername, apiPassword, apiSignature, apiMode', 'required'),
            array('email', 'email'),
            array('email, apiUsername, apiPassword', 'length', 'max'=>100),
            array('apiSignature', 'length', 'max'=>200),
            array('apiMode', 'boolean'),
         ));
    }  
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'email' => Sii::t('sii','Paypal Email Address'),
            'apiUsername' => Sii::t('sii','API Username'),
            'apiPassword' => Sii::t('sii','API Password'),
            'apiSignature' => Sii::t('sii','API Signature'),
            'apiMode' => Sii::t('sii','API Mode'),
        ));
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return array(
            'email' => Sii::t('sii','Enter the email address to receive Paypal payments. If you do not have it, PayPal will send you an email containing instructions to complete your PayPal account setup to receive payment when you receive your first order.'),
        );
    }  
    
    public function getNameTemplate($locale=null)
    {
        return PaymentMethod::getName(PaymentMethod::PAYPAL_EXPRESS_CHECKOUT, $locale);
    }
    
    public function getDescriptionTemplate($locale=null)
    {
        return Sii::t('sii','To use this payment method, you first need to have an Paypal account.{newline}',array('{newline}'=>PHP_EOL),null,$locale)
              .Sii::t('sii','{newline}We accept Paypal to pay for your order.',array('{newline}'=>PHP_EOL),null,$locale);
    }
    
    public static function getModes($mode=null)
    {
        if (!isset($mode)){
            return array(
                0=>Sii::t('sii','Sandbox'),
                1=>Sii::t('sii','Live'),
            );
        }
        else {
            $modes = self::getModes();
            return $modes[$mode];
        }
    }
    
    public function renderViewInCart($cartPaymentMethodForm)
    {
        return Yii::app()->controller->renderPartial($this->getViewFileBaseAlias().'.'.'._cart_paypal_express',array('model'=>$cartPaymentMethodForm),true);
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
            'buttonMethod'=>PaymentMethod::PAYPAL_EXPRESS_CHECKOUT,
            'buttonDisable'=>false,
            'buttonClick'=> new CJavaScriptExpression('paypalexpresscheckout('.$params['shopId'].',1,true);'),//override shipping address
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
        $decoded = json_decode($trace);        
        if (is_object($decoded))//embedded object 
            return Helper::htmlSmartKeyValues($decoded);
        else
            return $decoded;
    }
    /**
     * Render confirmation snippet upon successful checkout (last step of checkout)
     * @param array $params Basic parameters
     * Format: array(
     *     'methodDesc' => ...,
     *     'trace_no' => ...,
     *     'note' => ...,
     * )
     * @param array $extraParams Extra payment data
     * @return string
     */
    public function renderConfirmationSnippet($params,$extraParams=null)
    {
        $html = CHtml::tag('div',array('class'=>'data-element'),Yii::app()->controller->renderPartial('common.modules.payments.views.logo.paypal'));
        return $html;
    }  
}
