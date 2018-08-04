<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.UpdateAction");
/**
 * Description of LanguageUpdateAction
 *
 * @author kwlok
 */
class LanguageUpdateAction extends UpdateAction 
{
    /**
     * Class of the form to update. Defaults to 'null'
     * @var string
     */
    public $form;
    /**
     * Form attributes to be assigned to model attributes; 
     * Defaults to null, meaning all attributes as listed in {@link attributeNames} will be returned.
     * If it is an array, only the attributes in the array will be assigned.
     * @var string
     */
    public $formAttributes;
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
     * Run the action
     */
    public function run() 
    {
        if (!isset($this->loadModelMethod))
            $form = $this->controller->loadModel($_GET[$this->loadModelAttribute]);
        else {
            if (isset($this->loadModelAttribute))
                $form = $this->controller->{$this->loadModelMethod}($_GET[$this->loadModelAttribute]); 
            else
                $form = $this->controller->{$this->loadModelMethod}(); 
        }

        if (!$form instanceof LanguageForm)
            throw new CException(Sii::t('sii','Invalid form type, expecting LanguageForm.'));
                    
        if ($this->controller->module->getServiceManager($this->serviceInvokeParam)===null)
            throw new CHttpException(500,Sii::t('sii','Service not found'));        
            
        if ($this->controller->module->getServiceManager($this->serviceInvokeParam)->checkObjectAccess(user()->getId(),$form->modelInstance,$this->serviceOwnerAttribute)){

            $this->controller->setPageTitle($this->getPageTitle($form));
        
            if (isset($_POST[$this->form])) {
            
                try {
                    //[1]assign values in raw form
                    if (!isset($this->setAttributesMethod))
                        $form->assignLocaleAttributes($_POST[$this->form],false,$this->excludeLocaleAttributes);
                    else 
                        $form = $this->controller->{$this->setAttributesMethod}($form,false);

                    if ($form->validateLocaleAttributes()){
                        //[2]now serialize multi-lang attribute values
                        $form->assignLocaleAttributes($_POST[$this->form],true,$this->excludeLocaleAttributes);

                        //[3]copy form attributes to model attributes
                        if (isset($this->setModelAttributesMethod))
                            $form = $this->controller->{$this->setModelAttributesMethod}($form);
                        else {
                            $form->modelInstance->attributes = $form->getAttributes($this->formAttributes);
                            logTrace(__METHOD__.' '.get_class($form->modelInstance), $form->modelInstance->attributes);
                        }   

                        //[4] call ServiceManager to update record
                        $skipCheckAccess = false;//since this is already done at above lines
                        $this->invokeService(array(user()->getId(),$form->modelInstance,$skipCheckAccess));
                    }
                    else {
                        logError(__METHOD__.' form validation error', $form->getErrors(), false);
                        throw new CException(Sii::t('sii','Validation Error'));
                    }
                    
                 } catch (CException $e) {
                    logTrace(__METHOD__.' ===== exception ========== ',$e->getMessage());
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
        else {
            logError('Unauthorized access', $form->getAttributes());
            throwError403(Sii::t('sii','Unauthorized Access'));
        }
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
            $this->flashTitle = Sii::t('sii','{model} Update');
        if (!isset($this->flashMessage))
            $this->flashMessage = Sii::t('sii','{name} is updated successfully');
        user()->setFlash(isset($this->flashId)?$this->flashId:get_class($model),array(
            'message'=>str_replace('{name}',isset($this->nameAttribute)?$model->displayLanguageValue($this->nameAttribute,user()->getLocale()):$model->displayName(),$this->flashMessage),
            'type'=>'success',
            'title'=>str_replace('{model}',$model->displayName(),$this->flashTitle),
        ));
    }    

}
