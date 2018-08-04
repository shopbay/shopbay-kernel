<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PaymentMethodSubForm
 *
 * @author kwlok
 */
abstract class PaymentMethodSubForm extends PaymentMethodForm 
{  
    protected $nameAttribute = 'name';
    /**
     * Validate attributes when they have empty values
     * @param type $attribute
     * @param type $value
     * @param type $locale
     */
    public function validateEmptyLocaleAttributes($attribute, $value, $locale)
    {
        //for non default locales, when name presents, instructions must also there
        //this rule is required as default locale has check only on "required" rule
        if (in_array($attribute, array_keys($this->localeAttributes()))){
            if ($locale!=$this->getLanguageDefaultLocale()){
                if (!empty($this->{$this->nameAttribute}[$locale]) && empty($this->{$attribute}[$locale])){
                    $this->addError($this->formatErrorName($locale, $attribute),SLocale::getLanguages($locale).' '.Sii::t('sii','{column} cannot be blank when name is presented',array('{column}'=>$this->getAttributeLabel($attribute))));
                }
            }
        }
    }    
    /**
     * Return local attributes to this form
     * @return Array of custom attributes to be stored in params 
     */
    public function paramsAttributes()
    {
        $parent = new PaymentMethodForm();
        $excludes = array_keys($parent->getAttributes());
        $attributes = array_diff(array_keys($this->getAttributes()), $excludes);// remove the elements of $excludes
        //logTrace(__METHOD__,$attributes);
        return $attributes;
    }
    /**
     * parameterize params attributes into a string
     */
    public function parameterizeAttributes($json=false)
    {
        if ($json){
            $params = "{";
            $size = count($this->paramsAttributes()) - 1;
            foreach ($this->paramsAttributes() as $index => $attribute) {
                $params .= '"'.$attribute.'":'.$this->$attribute; 
                if ($index < $size)
                    $params .= ',';
            }
            $params .= "}";
            
        }
        else {
            $params = new CMap();
            foreach ($this->paramsAttributes() as $index => $attribute) {
                $params->add($attribute,$this->$attribute);
            }
            $params = json_encode($params->toArray());
        }
        $this->params = $params;
        return $this->params;
    }    
    /**
     * Serialize multi-lang attributes 
     * @param array $form form attributes (or model->getAttributes())
     * @param boolean $serialize Json encode into string
     * @param array $exclude attributes to be excluded from assignment
     */
    public function assignLocaleAttributes($form,$serialize=false,$exclude=array())
    {
        parent::assignLocaleAttributes($form, $serialize, $exclude);
        $this->parameterizeAttributes($serialize);
    }       
    /**
     * Overridden method
     * @param $skipSubFormValidation default to false
     * @return boolean
     */
    public function validateLocaleAttributes()
    {
        logTrace(__METHOD__.' scenario '.$this->getScenario());
        
        foreach ($this->getLocaleAttributes() as $attribute => $value) {
            //exclude sub form validation here
            if (in_array($attribute, $this->getAttributeExclusion())==false){
                if (is_array($value)){
                    foreach ($value as $locale => $localValue) {
                        $this->setCurrentLocale($locale);
                        if (!empty($localValue)){
                            $this->validateLocaleAttribute($attribute, $localValue, $locale);
                        }
                        else {
                            //validate agains empty attribute against other attributes (rules)
                            $this->validateEmptyLocaleAttributes($attribute, $localValue, $locale);
                            //this rule make sure that at least the shop locale (default) must exists
                            if ($locale==$this->getLanguageDefaultLocale())
                                $this->validateLocaleAttribute($attribute, $localValue, $locale);
                        }
                    }
                }
                else {
                    $this->validateLocaleAttribute($attribute, $value);
                }
            }            
        }
        return !$this->hasErrors();
    }     
    /**
     * Clone a form for validation use
     * @return \form
     */
    protected function cloneForm()
    {
        $validatingForm = get_class($this);
        return new $validatingForm();
    }    
    /**
     * Load attribute template based on shop locales
     * Child class must implement method below to support custom attribute template e.g.
     *   self::getNameTemplate
     *   self::getDescriptionTemplate
     * 
     * @param integer $shopId The shop's locales to be based on
     * @throws CException
     */
    protected function loadLocaleAttributeTemplates($shopId) 
    {
        $this->shop_id = $shopId;
        $templates = new CMap();
        foreach (array_keys($this->localeAttributes()) as $attribute) {
            $loadTemplate = false;
            foreach (array_keys($this->locales()) as $locale) {
                $methodName = 'get'.ucfirst($attribute).'Template';
                if (method_exists($this, $methodName)){
                    $templates->add($locale,$this->$methodName($locale));
                    $loadTemplate = true;
                }
            }        
            if ($loadTemplate)
                $this->$attribute = json_encode($templates->toArray());
        }
    }
    /**
     * @return A method to return view file
     */
    abstract public function getViewFile();
    /**
     * Render the payment method view / form in Shopping cart
     * @param /modules/carts/models/CartPaymentMethodForm $cartPaymentMethodForm
     */
    abstract public function renderViewInCart($cartPaymentMethodForm);
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
    abstract public function onMethodSelected($params);
    /**
     * Trace no parsing method; Apply to external plugin such as PayPal, Braintree etc
     * @see Model Payment->trace_no
     * @param $trace raw trace stored in Payment model record
     * @return mixed False if not parsing required; Else, will return parsing result.
     */
    abstract public function parseTraceNo($trace);
    /**
     * Fetch payment data from HTTP POST 
     */
    abstract public function fetchPaymentData();    
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
     *  @return string
     */
    abstract public function renderConfirmationSnippet($params,$extraParams=null);    
    /**
     * Use for Braintree PayPal 
     * Get the shipping addresss in json to be passed to Paypal during checkout (shipping address override)
     * @param type $addressForm carts/models/CartAddressForm
     * @throws CException
     */
    abstract public function getOverridenShippingAddress($addressForm);
    
}
