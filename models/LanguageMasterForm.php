<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of LanguageMasterForm
 *
 * @author kwlok
 */
abstract class LanguageMasterForm extends LanguageForm
{
    /*
     * Name of slave form attribute
     */
    protected $slaveFormAttribute;
    /**
     * Initializes this form.
     */
    public function init()
    {
        parent::init();
        $this->setAttributeExclusion(array($this->slaveFormAttribute));
    }  
    /**
     * A boolean implementation to decide if to invoke slave form validation
     * @return boolean
     */
    abstract public function invokeSlaveFormValidation();
    /*
     * Method to validate slave form
     */
    public function validateSlaveForm()
    {
        if ($this->{$this->slaveFormAttribute}==null)
            $this->addError('id',Sii::t('sii','"{object}" cannot be blank',array('{object}'=>$this->slaveFormAttribute)));
                
        if ($this->{$this->slaveFormAttribute} != null){
            $this->{$this->slaveFormAttribute}->validateLocaleAttributes();

            if ($this->{$this->slaveFormAttribute}->hasErrors()){
                logTraceDump(__METHOD__.' slave form errors',$this->{$this->slaveFormAttribute}->getErrors());
                $this->addErrors($this->{$this->slaveFormAttribute}->getErrors());
            }     
        }
    } 
    /**
     * Overridden method
     * @return boolean
     */
    public function validateLocaleAttributes()
    {
        logTrace(__METHOD__.' scenario '.$this->getScenario());
        
        foreach ($this->getLocaleAttributes() as $attribute => $value) {
            //exclude slave form validation here
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
        //Here we do the slave form validation 
        if ($this->invokeSlaveFormValidation())
            $this->validateSlaveForm();
        
        return !$this->hasErrors();
    }    
    /**
     * Overridden method
     */
    protected function cloneForm()
    {
        $validatingForm = get_class($this);
        return new $validatingForm($this->id);
    }     
   
}
