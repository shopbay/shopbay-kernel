<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.CreateAction");
/**
 * Description of LanguageCreateAction
 *
 * @author kwlok
 */
class LanguageCreateAction extends CreateAction
{    
    /**
     * Locale attributes to be exluded from form attributes assignment; 
     * @var array
     */
    public $excludeLocaleAttributes = [];    
    /**
     * A callback method to set model attributes; 
     * If not set, it will do a direct attribute mapping from $_POST 
     * If set, it is always expect passing in argument to be $model/$form itself, and 
     * returning $model/$form also
     * 
     * Example: At controller side, there is a method callback "setModelAttributesMethod($form)
     * 
     * public function setModelAttributesMethod($form)
     * {
     *     $form->attributes=$_POST['ABCForm'];
     *     //do other logic
     *     //...
     *     return $form;
     * }
     * @var array
     */
    public $setModelAttributesMethod;    
    /**
     * Run the action. Default to "form" mode.
     */
    public function run() 
    {
        if (isset($this->form))
            $this->_formMode();
        else
            throw new CException(Sii::t('sii',__CLASS__.' must run in form mode.'));
    }      
    /**
     * Run in "form" mode - Expect to pass in $form
     */
    private function _formMode() 
    {
        if (isset($this->createModelMethod))
            $form = $this->controller->{$this->createModelMethod}();
        else
            $form = new $this->form;

        if (!$form instanceof LanguageForm)
            throw new CException(Sii::t('sii','Invalid form type, expecting LanguageForm.'));
            
        $this->controller->setPageTitle($this->getPageTitle($form));
        
        if(isset($_POST[$this->form])){

            //[1]assign values in raw form
            if (isset($this->setAttributesMethod))
                $form = $this->controller->{$this->setAttributesMethod}($form,false);
            else
                $form->assignLocaleAttributes($_POST[$this->form],false,$this->excludeLocaleAttributes);
            
            //[2]assign account_id 
            $form->account_id = user()->getId();
                    
            try {
                        
                if ($form->validateLocaleAttributes()){
                    //[3]now serialize multi-lang attribute values
                    $form->assignLocaleAttributes($_POST[$this->form],true,$this->excludeLocaleAttributes);
                    
                    //[4]copy form attributes to model attributes
                    if (isset($this->setModelAttributesMethod))
                        $form = $this->controller->{$this->setModelAttributesMethod}($form);
                    else {
                        $form->modelInstance->attributes = $form->getAttributes($this->formAttributes);
                        logTrace(__METHOD__.' '.get_class($form->modelInstance), $form->modelInstance->attributes);
                    }
                    //[5] call ServiceManager to create record
                    $this->invokeService([user()->getId(),$form->modelInstance]);
                }
                else {
                    logError(__METHOD__.' form validation error', $form->getErrors(), false);
                    throw new CException(Sii::t('sii','Validation Error'));
                }
                
            } catch (CException $e) {
                logTrace(__METHOD__.' ===== exception ==========',$e->getTraceAsString());
                //assign values in raw form but multi-lang (json formatted) value
                if (isset($this->setAttributesMethod))
                    $form = $this->controller->{$this->setAttributesMethod}($form,true);
                else
                    //serialize multi-lang attribute values before returning error
                    $form->assignLocaleAttributes($_POST[$this->form],true,$this->excludeLocaleAttributes);
                $this->setErrorFlash($form,$e);
            }
        }
        
        $this->renderPage($form);        
    }      
    /**
     * Generate success flash
     * 
     * @param type $model
     * @return type
     */
    protected function setSuccessFlash($model)
    {
        if (!isset($this->flashTitle))
            $this->flashTitle = Sii::t('sii','{model} Creation');
        if (!isset($this->flashMessage))
            $this->flashMessage = Sii::t('sii','{name} is created successfully');
        user()->setFlash(isset($this->flashId)?$this->flashId:get_class($model),array(
            'message'=>str_replace('{name}',isset($this->nameAttribute)?$model->displayLanguageValue($this->nameAttribute,user()->getLocale()):$model->displayName(),$this->flashMessage),
            'type'=>'success',
            'title'=>str_replace('{model}',$model->displayName(),$this->flashTitle),
        ));
    }    
    
}
