<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PaymentMethodPluginForm
 *
 * @author kwlok
 */
abstract class PaymentMethodPluginForm extends PaymentMethodSubForm 
{
    protected $pluginName;
    protected $viewFileName;
    protected $excludeAttributes = [];
    /*
     * Attributes to persists its value while doing validation; 
     * As validation is done attribute by attribute, 
     * Certain validation rules requires other attribute value to be presented requires this feature
     */
    protected $persistentAttributes = ['shop_id','name','description'];      
    /*
     * Local attributes
     */    
    public $description;
    /**
     * @return array of attributes required to support locales
     */
    public function localeAttributes() 
    {
        return array_merge(parent::localeAttributes(),array(
            'description'=>array(
                'required'=>true,
                'inputType'=>'textArea',
                'inputHtmlOptions'=>array('rows'=>8,'maxlength'=>500),
                'note'=>array(
                    PaymentMethodForm::getNote2(),
                ),
            ),
        ));
    }      
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('shop_id, name, description', 'required'),
            array('name', 'length', 'max'=>50),
            array('description', 'length', 'max'=>500),
            array('description', 'rulePurify'),
        );
    }  
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'name' => Sii::t('sii','Name'),
            'description' => Sii::t('sii','Description'),
        );
    }
    
    public function getViewFileBaseAlias() 
    {
        return 'common.modules.payments.plugins.'.$this->pluginName.'.views';
    }
    
    public function getViewFile() 
    {
        return $this->getViewFileBaseAlias().'.'.$this->viewFileName;
    }
    /**
     * Overridden method
     * @return boolean
     */
    public function validateLocaleAttributes()
    {
        $this->setAttributeExclusion($this->excludeAttributes);
        return parent::validateLocaleAttributes();
    }       
    /**
     * Return local attributes to this form
     * @return Array of custom attributes to be stored in params 
     */
    public function paramsAttributes()
    {
        $parent = new PaymentMethodForm();
        $excludes = array_merge($this->excludeAttributes,array_keys($parent->getAttributes()));
        $attributes = array_diff(array_keys($this->getAttributes()), $excludes);// remove the elements of $excludes
        //logTrace(__METHOD__,$attributes);
        return $attributes;
    }    
    /**
     * Check if require any payment data for processing during cart checkout (when payment method is selected)
     * @return boolean Default to false
     */
    public function requirePaymentDataOnCheckout()
    {
        return false;
    }
    /**
     * Fetch payment data from HTTP POST 
     * @return null Default to null
     */
    public function fetchPaymentData()
    {
        return null;        
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
        $html = CHtml::tag('div',array('class'=>'data-element'),$params['methodDesc']);
        $html .= CHtml::tag('div',array('class'=>'data-element'),$params['trace_no']);
        $html .= CHtml::tag('div',array('class'=>'data-element'),$params['note']);
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
        return null;
    }     
}
