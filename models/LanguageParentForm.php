<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of LanguageParentForm
 *
 * @author kwlok
 */
abstract class LanguageParentForm extends LanguageForm 
{
    /*
     * The parent key attribute
     * If set, it is mandatory for object construction
     */
    protected $parentKeyAttribute;
    /**
     * Inherited attributes to be excluded and not applicable
     * @see LanguageForm
     */
    protected $exclusionAttributes = [];
    /*
     * The child key attribute
     * If set, it is mandatory for child object construction
     */
    protected $childFormKeyAttribute;
    /*
     * The child form class name
     */
    protected $childFormClass;
    /*
     * The child form attribute name
     */
    protected $childFormAttribute;
    /*
     * The child form default scenario
     */
    protected $childFormScenario = 'update';
    /*
     * The model attributes to get copied from form attributes
     * If empty, it means copy all attributes
     */
    protected $childFormModelAttributes = [];
    /**
     * Initializes this form.
     */
    public function init()
    {
        parent::init();
        $this->setAttributeExclusion($this->exclusionAttributes);
    }     
    /*
     * Method to validate child form
     */
    abstract public function validateChildForm();      
    /**
     * Overridden method
     * @return boolean
     */
    public function validateLocaleAttributes()
    {
        logTrace(__METHOD__.' scenario '.$this->getScenario());
        
        foreach ($this->getLocaleAttributes() as $attribute => $value) {
            //exclude child form validation here
            if (in_array($attribute, $this->getAttributeExclusion([$this->childFormAttribute]))==false){
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
        //Here we do the child form validation 
        $this->validateChildForm();
        
        return !$this->hasErrors();
    } 
    /**
     * Overridden method
     */
    protected function cloneForm()
    {
        $validatingForm = get_class($this);
        if (isset($this->parentKeyAttribute)){
            $form = new $validatingForm($this->parentKeyAttribute);
        }
        else {
            $form = new $validatingForm();
        } 
        return $form;
    }  
    /**
     * Load locale attributes from model instance 
     * @param array $exclude attributes to be excluded from loading
     */
    public function loadLocaleAttributes($exclude=[])
    {
        foreach ($this->getLocaleAttributeKeys() as $attribute) {
            if (in_array($attribute, $exclude)==false)
                $this->$attribute = $this->modelInstance->$attribute;
        }
        $this->{$this->childFormAttribute} = $this->modelInstance->{$this->childFormAttribute};
        $this->checkIsNewRecord();
    }   
    /**
     * @return model attributes to be copied
     */
    public function getModelAttributes()
    {
        $attributes = $this->getAttributes();
        unset($attributes['model']);
        unset($attributes[$this->childFormAttribute]);
        return $attributes;
    }           
    /**
     * Transform inner form into inner models
     * @return \model
     */
    public function getChildModels()
    {
        $models = [];
        foreach ($this->{$this->childFormAttribute} as $key => $form) {
            $model = new $form->model($this->childFormKey);
            $model->setScenario($this->getScenario());
            $model->attributes = $form->getAttributes($this->childFormModelAttributes);
            $models[] = $model;            
            //logTrace(__METHOD__,$model->attributes);
        }
        return $models;
    }
    /**
     * Transform inner models into inner forms
     * @return \model
     */
    public function getChildForms()
    {
        $forms = [];
        foreach ($this->modelInstance->{$this->childFormAttribute} as $model) {
            $scenario = $this->getScenario()!=null?$this->getScenario():$this->childFormScenario;
            $form = new $this->childFormClass($this->childFormKey,$scenario,$this->modelInstance->id);
            $form->attributes = $model->attributes;
            $forms[] = $form;
            //logTrace(__METHOD__.' form attributes',$form->attributes);
        }
        return $forms;
    }     
    
    protected function getChildFormKey()
    {
        if (isset($this->childFormKeyAttribute)){
            return $this->{$this->childFormKeyAttribute};
        }
        else {
            return $this->id;
        } 
    }
    /**
     * Instantiate child form
     * @param type $data
     * @param type $skipValidationAttribute attribute to skip validation (assigned with temp value)
     * @return \childFormClass
     */    
    public function instantiateChildForm($data,$skipValidationAttribute=null)
    {
        //[1]instantiate child form
        $childform = new $this->childFormClass($data[$this->childFormKeyAttribute]);
        $childform->assignLocaleAttributes($data,true);//serialize multi-lang attribute values
        if (!isset($skipValidationAttribute)){
            $skipValidationAttribute = $this->childFormKeyAttribute;
        }
        if ($childform->$skipValidationAttribute==null)
            $childform->$skipValidationAttribute = 0;//temp assigned id for validation use only
        //[2]transfer back errors to child from, if any, from previous activity
        foreach ($this->{$this->childFormAttribute} as $attribute)
            $childform->addErrors($attribute->getErrors());

        logTrace(__METHOD__.' attributes',$childform->getAttributes());

        return $childform;
    }
    
}
