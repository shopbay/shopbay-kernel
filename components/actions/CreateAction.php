<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.CRUDBaseAction");
/**
 * Description of CreateAction
 *
 * @author kwlok
 */
class CreateAction extends CRUDBaseAction 
{
    /**
     * The service name to invoke in ServiceManager. Defaults to 'create'
     * @var string
     */
    public $service = 'create';
    /**
     * Class of the form to create. Defaults to 'null'
     * If set, action will run in form mode
     * @var string
     */
    public $form;
    /**
     * View file name. Defaults to 'create'
     * @var string
     */
    public $viewFile = 'create';
    /**
     * Form attributes to be assigned to model attributes; 
     * Defaults to null, meaning all attributes as listed in {@link attributeNames} will be returned.
     * If it is an array, only the attributes in the array will be assigned.
     * @var string
     */
    public $formAttributes;
    /**
     * A callback method to create model based on own requirement; 
     * If not set, it will do a direct calling new $this->model 
     * If set, it is always expect returning the created $model 
     * 
     * Example: At controller side, there is a method callback "createModelMethod($id)
     * 
     * public function createModelMethod()
     * {
     *     //do other logic
     *     //...
     *     return $model;
     * }
     * @var array
     */
    public $createModelMethod;
    /**
     * A callback method to set form attributes; 
     * If not set, it will do a direct attribute mapping from $_POST 
     * If set, it is always expect passing in argument to be $model/$form itself, and 
     * returning $model/$form also
     * 
     * Example: At controller side, there is a method callback "setAttributesMethod($form)
     * 
     * public function setAttributesMethod($form)
     * {
     *     $form->attributes=$_POST['ABCForm'];
     *     //do other logic
     *     //...
     *     return $form;
     * }
     * @var array
     */
    public $setAttributesMethod;
    /**
     * Run the action. Default to "model" mode.
     * If $form is set, it will run on "form" mode.
     */
    public function run() 
    {
        if (isset($this->form))
            $this->_formMode();
        else
            $this->_modelMode();
    }     
    /**
     * Run in "model" mode - Expect to pass in $model
     */
    protected function _modelMode() 
    {
        if (isset($this->createModelMethod))
            $model = $this->controller->{$this->createModelMethod}();
        else
            $model = new $this->model;

        $this->controller->setPageTitle($this->getPageTitle($model));
        
        if(isset($_POST[$this->model])){

            try {
                        
                if (isset($this->setAttributesMethod))
                    $model = $this->controller->{$this->setAttributesMethod}($model);
                else 
                    $model->attributes = $_POST[$this->model];

                $this->invokeService(array(user()->getId(),$model));
                
            } catch (CException $e) {
                $this->setErrorFlash($model,$e);
            }
        }
        
        $this->renderPage($model);
        
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

        $this->controller->setPageTitle($this->getPageTitle($form));
                
        if(isset($_POST[$this->form])){

            if (isset($this->setAttributesMethod))
                $form = $this->controller->{$this->setAttributesMethod}($form);
            else
                $form->attributes = $_POST[$this->form];
            
            $model = new $this->model;

            try {
                        
                if ($form->validate()){
                    $model->attributes = $form->getAttributes($this->formAttributes);
                    $this->invokeService(array(user()->getId(),$model));
                }
                else {
                    logError(__METHOD__.' form validation error', $form->getErrors(), false);
                    throw new CException(Sii::t('sii','Validation Error'));
                }
                
            } catch (CException $e) {
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
        parent::setSuccessFlash($model);
    }    
    /**
     * Generate error flash
     * 
     * @param type $model
     * @return type
     */
    protected function setErrorFlash($model,$exception)
    {
        if (!isset($this->flashTitle))
            $this->flashTitle = Sii::t('sii','{model} Creation');
        parent::setErrorFlash($model,$exception);
    }    
    /**
     * Generate page title
     * 
     * @param type $model
     * @return type
     */
    protected function getPageTitle($model)
    {
        if (isset($this->htmlPageTitle))
            return $this->htmlPageTitle;
        else
            return Sii::t('sii','Create {model}',array('{model}'=>$model->displayName()));
    }    
}
