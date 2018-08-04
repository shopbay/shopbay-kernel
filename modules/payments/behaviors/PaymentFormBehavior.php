<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PaymentFormBehavior
 *
 * It has the basic json decoded data elements:
 * <pre>
 * array(
 *  'id'=>'<payment_method_id>',
 *  'modelClass'=>'<payment_method_model_classname>',
 *  'name'=>'<payment_method_name>',
 *  'mode'=>'<payment_method_mode>',//equivalent to CActiveRecord PaymentMethod->method
 *  'offline'=>'<offline_payment_method>',//true or false to determine if is a offline payment method
 * );
 * </pre>
 * 
 * @author kwlok
 */
class PaymentFormBehavior extends CBehavior 
{
    /**
    * @var string The name of payment_method attribute that stores json encoded payment_method data. Defaults to "payment_method"
    */
    public $paymentMethodAttribute = 'payment_method';
    /*
     * Internally used json encoded data 
     */
    private $_d;
    /*
     * Internally used to store payment method model
     */
    private $_m;
    
    public function getPaymentMethodData() 
    {
        if ($this->_d==null)
            $this->_d = json_decode($this->getOwner()->{$this->paymentMethodAttribute});
        return $this->_d;
    }
    public function hasPaymentMethod() 
    {
        return $this->getPaymentMethodData()!=null;
    }    
    public function getPaymentMethodModel()
    {
        if ($this->_m===null){
            if (!$this->hasPaymentMethod())
                throw new CException(Sii::t('sii','PaymentFormBehavior has no data'));
            $type = $this->getPaymentMethodModelClass();
            $model = $type::model()->findByPk($this->getPaymentMethodId());
            if ($model!=null){
                $this->_m = $model;
            }
        }
        return $this->_m;
    }
    public function getPaymentMethodId() 
    {
        return $this->hasPaymentMethod()?$this->getPaymentMethodData()->id:null;
    }    
    public function getPaymentMethodModelClass() 
    {
        return $this->hasPaymentMethod()?$this->getPaymentMethodData()->modelClass:null;
    }    
    public function getPaymentMethodMode() 
    {
        if ($this->hasPaymentMethod() && isset($this->getPaymentMethodData()->mode))
            return $this->getPaymentMethodData()->mode;
        else
            return $this->getOwner()->{$this->paymentMethodAttribute};        
    }        
    public function getPaymentMethodName($locale=null)             
    {
        if ($this->hasPaymentMethod() && isset($this->getPaymentMethodData()->name)){
            $name = $this->getPaymentMethodData()->name;
            if (is_scalar($name)){
                return $name;
            }    
            else {
                return $this->getOwner()->parseLanguageValue($name,isset($locale)?$locale:$this->getOwner()->getLocale());
            }
        }
        else {
            return PaymentMethod::getName($this->getOwner()->{$this->paymentMethodAttribute},$locale);
        }
    }    
    
    public function isOfflinePaymentMethod()
    {
        return $this->hasPaymentMethod()?$this->getPaymentMethodData()->offline:false;
    }
}

