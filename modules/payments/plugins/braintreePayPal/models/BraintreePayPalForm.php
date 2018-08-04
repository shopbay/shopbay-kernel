<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BraintreePayPalForm
 *
 * @author kwlok
 */
class BraintreePayPalForm extends PaymentMethodPluginForm 
{
    protected $pluginName = 'braintreePayPal';
    protected $viewFileName = '_form_braintree_paypal';
    /*
     * Local attributes
     */    
    public $publicKey;
    public $privateKey;
    public $merchantId;
    public $merchantAccountId;
    public $apiMode = 'sandbox';//sandbox or production
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            array('publicKey, privateKey, merchantId, merchantAccountId, apiMode', 'required'),
            array('publicKey, privateKey, merchantId, merchantAccountId', 'length', 'max'=>100),
        ]);
    }  
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'publicKey' => Sii::t('sii','Public Key'),
            'privateKey' => Sii::t('sii','Private Key'),
            'merchantId' => Sii::t('sii','Merchant ID'),
            'merchantAccountId' => Sii::t('sii','Merchant Account ID'),
            'apiMode' => Sii::t('sii','API Mode'),
        ]);
    }
    /**
     * @return array customized attribute tooltips (name=>label)
     */
    public function attributeToolTips()
    {
        return [
            'publicKey' => Sii::t('sii','This is the public key for your Braintree account.'),
            'privateKey' => Sii::t('sii','This is the private key for your Braintree account.'),
            'merchantId' => Sii::t('sii','This is the merchant ID for your Braintree account.'),
            'merchantAccountId' => Sii::t('sii','This is the merchant account ID used to create a transaction. Its currency must match with your shop\'s currency.'),
        ];
    }  
    
    public function getNameTemplate($locale=null)
    {
        return Sii::tl('sii', 'PayPal', $locale);
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
                'sandbox'=>Sii::t('sii','Sandbox'),
                'production'=>Sii::t('sii','Production'),
            );
        }
        else {
            $modes = self::getModes();
            return $modes[$mode];
        }
    }    
    
    public function renderViewInCart($cartPaymentMethodForm)
    {
        $cartPaymentMethodForm->formUrl = url('cart/management/SelectPaymentMethod');
        return Yii::app()->controller->renderPartial($this->getViewFileBaseAlias().'.'.'_cart_braintree_paypal',array('model'=>$cartPaymentMethodForm),true);
    }
    /**
     * Define the payment method selection onClick behavior (at shopping cart)
     * @see /modules/carts/actions/ButtonGetAction
     * @see /extensions/braintree/assets/braintree-custom.js loadbraintree()
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
            'buttonMethod'=>PaymentMethod::BRAINTREE_PAYPAL,
            'buttonDisable'=> false,
            'buttonClick'=> new CJavaScriptExpression('processbraintree("'.$params['formId'].'");'),//NOT IN USE
            'callback' => new CJavaScriptExpression('loadbraintreepaypal("'.$params['formId'].'","'.$params['buttonName'].'",'.$params['methodId'].','.$params['amount'].');'),
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
     * Check if require any payment data for processing during cart checkout (when payment method is selected)
     * @return boolean Default to false
     */
    public function requirePaymentDataOnCheckout()
    {
        return true;
    }  
    /**
     * Fetch payment data from HTTP POST 
     * @see braintree-paypal.js
     */
    public function fetchPaymentData()
    {
        return array(
            'nonce'=>$_POST['payment_method_nonce'],
        );        
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
     * Format: array(
     *     'cardType' => ...,
     *     'iconBaseUrl' => ...,
     *     'lastTwo' => ...,
     *     'last4' => ...,
     * )  
     * @return string
     **/
    public function renderConfirmationSnippet($params,$extraParams=null)
    {
        $html = CHtml::tag('div',array('class'=>'data-element'),Yii::app()->controller->renderPartial('common.modules.payments.views.logo.paypal'));
        return $html;
    }     
    /**
     * Use for Braintree PayPal 
     * Get the shipping addresss in json to be passed to Paypal during checkout (shipping address override)
     * @param type $addressForm carts/models/CartAddressForm
     * @throws CException
     */
    public function getOverridenShippingAddress($addressForm)
    {
        if ($addressForm==null)
            throw new CException('Address is required for address overriden');
        //override shipping address
        $shippingAddressOverride = [
            'recipientName' => $addressForm->recipient,
            'streetAddress' => $addressForm->address1,
            'extendedAddress' => $addressForm->address2,
            'locality' => $addressForm->city,
            'countryCodeAlpha2' => $addressForm->country,
            'postalCode' => $addressForm->postcode,
            'region' => $addressForm->state,
            'phone' => $addressForm->mobile,
            //true allows consumer to choose a different shipping address than what was passed in. 
            //false does not allow the consumer to edit the shipping address.
            'editable' => true,
        ];
        return json_encode($shippingAddressOverride);
    }     
}
