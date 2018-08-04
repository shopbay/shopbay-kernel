<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of CartPaymentMethodForm
 *
 * @author kwlok
 */
class CartPaymentMethodForm extends PaymentForm 
{
    public $method = PaymentMethod::UNDEFINED;
    public $method_desc;
    public $paid;//indicator if payment is successful
    public $formUrl;//form url to submit after payment is selected (or filled); Use in Braintree etc
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('id, shop_id, method, currency, amount', 'required'),
            array('method', 'safe'),
            array('method', 'verifyMethod'),
            array('trace_no, reference_no, note', 'safe'),
        );
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'shop_id' => Sii::t('sii','Shop'),
            'id' => Sii::t('sii','Payment Method'),
            'method' => Sii::t('sii','Payment Method'),
            'payer' => Sii::t('sii','Buyer'),
            'amount'=>Sii::t('sii','Amount'),
            'trace_no' => Sii::t('sii','Transaction Reference No'),
            'note' => Sii::t('sii','Fund Transfer Note'),
            'verify_code'=>Sii::t('sii','Verification Code'),                
        );
    }        
    /**
     * Return payment method description
     * @return Shop
     */    
    public function getMethodDesc($locale=null)
    {
        if (isset($this->method_desc))
            return $this->parseLanguageValue($this->method_desc,$locale);
        else {
            return Sii::tl('sii','unset',$locale);
        }
    }    
    /**
     * Return payment tips
     * @return Shop
     */    
    public function getTips($locale=null)
    {
        if ($this->hasPaymentMethod())
            return $this->getPaymentMethodModel()->getDescription($locale);
        else 
            return Sii::tl('sii','unset',$locale);
    }
    /**
     * Render payment method tips
     */
    public function renderTips($excludeMethod=null)
    {
        $tips = '';
        foreach ($this->getPaymentMethods($excludeMethod) as $paymentMethodModel){
            $this->id = $paymentMethodModel->id;//assign payment method id
            $this->method = $paymentMethodModel->method;//assign method
            $form = PaymentMethod::getFormInstance($this->method);
            $tips .= $form->renderViewInCart($this);
            $this->method = PaymentMethod::UNDEFINED;//restore back original method
        }
        echo $tips;
    }
    
    public function requirePaymentData()
    {
        $form = PaymentMethod::getFormInstance($this->method);
        return $form->requirePaymentDataOnCheckout();
    }
    
    public function fetchPaymentData()
    {
        $form = PaymentMethod::getFormInstance($this->method);
        $this->extraPaymentData = $form->fetchPaymentData();
        logTrace(__METHOD__,$this->extraPaymentData);
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
    public function renderConfirmationSnippet()
    {
        $form = PaymentMethod::getFormInstance($this->method);
        $params = array(
            'methodDesc'=>$this->getMethodDesc(user()->getLocale()),
            'trace_no'=>$this->trace_no,
            'note'=>$this->note,
        );
        return $form->renderConfirmationSnippet($params,isset($this->extraPaymentData)?$this->extraPaymentData:null);
    }
    /**
     * Use for Braintree PayPal 
     * Get the shipping addresss in json to be passed to Paypal during checkout (shipping address override)
     * @param type $addressForm carts/models/CartAddressForm
     * @throws CException
     */
    public function getOverridenShippingAddress($addressForm)
    {
        $form = PaymentMethod::getFormInstance($this->method);
        return $form->getOverridenShippingAddress($addressForm);
    }     
}