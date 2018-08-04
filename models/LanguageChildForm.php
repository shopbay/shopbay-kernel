<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of LanguageChildForm
 *
 * @author kwlok
 */
abstract class LanguageChildForm extends LanguageForm 
{
    /*
     * The primary key attribute and is mandatory for object construction
     */
    public $keyAttribute;
    /**
     * Inherited attributes to be excluded and not applicable
     * @see LanguageForm
     */
    protected $exclusionAttributes = array('account_id','shop_id');
    /**
     * Customized Constructor.
     * Make shipping_id is a mandatory argument
     * 
     * @see LanguageForm::__construct()
     */
    public function __construct($key,$scenario='',$id=null)
    {
        parent::__construct($id,$scenario);
        $this->id = $id;
        $this->{$this->keyAttribute} = $key;
    }        
    /**
     * Initializes this form.
     */
    public function init()
    {
        parent::init();
        //Child form does not have these inherited attributes
        $this->setAttributeExclusion($this->exclusionAttributes);
    }   
    /**
     * Overridden method
     * @return boolean
     */
    public function validateLocaleAttributes()
    {
        $this->vectorizeLocaleAttributes();
        foreach ($this->getLocaleAttributes() as $attribute => $value) {
            if (in_array($attribute, $this->getAttributeExclusion())==false){
                if (is_array($value)){
                    foreach ($value as $locale => $localValue) {
                        $this->setCurrentLocale($locale);
                        if (!empty($localValue))
                            $this->validateLocaleAttribute($attribute, $localValue, $locale);
                        else {
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
        $this->devectorizeLocaleAttributes();
        return !$this->hasErrors();
    }    
    /**
     * Overridden method
     */
    protected function cloneForm()
    {
        $childForm = get_class($this);
        return new $childForm($this->{$this->keyAttribute});
    }    
    /**
     * Custom attribute name
     * @param type $locale
     * @param type $attribute
     * @return type
     */
    public function formatAttributeName($locale,$attribute)
    {
        if (isset($locale))
            return get_class($this).'['.$this->id.']['.$attribute.']['.$locale.']';
        else
            return get_class($this).'['.$this->id.']['.$attribute.']';
    }    
    /**
     * Custom error name
     * @param type $locale
     * @param type $attribute
     * @return type
     */    
    public function formatErrorName($locale,$attribute)
    {
        if (isset($locale))
            return 'childform_'.$attribute.'_'.$locale.$this->id;
        else
            return 'childform_'.$attribute.'_'.$this->id;
    }    
}
