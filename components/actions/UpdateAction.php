<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.CRUDBaseAction");
/**
 * Description of UpdateAction
 *
 * @author kwlok
 */
class UpdateAction extends CRUDBaseAction 
{
    /**
     * The service name to invoke in ServiceManager. Defaults to 'update'
     * @var string
     */
    public $service = 'update';
    /**
     * View file name. Defaults to 'update'
     * @var string
     */
    public $viewFile = 'update';
    /**
     * A callback method to load model based on model id; 
     * If not set, it will do a direct calling controller->loadModel($id) method 
     * If set, it is always expect passing in $id and return $model 
     * 
     * Example: At controller side, there is a method callback "loadModelMethod($id)
     * 
     * public function loadModelMethod($id)
     * {
     *     //do other logic
     *     //...
     *     return $model;
     * }
     * @var array
     */
    public $loadModelMethod;
    /**
     * Indicate if the attribute of model for loading; Default to "null".
     * 
     */
    public $loadModelAttribute = 'id';
    /**
     * A callback method to set model attributes; 
     * If not set, it will do a direct attribute mapping from $_POST[$this->model] 
     * If set, it is always expect passing in argument to be $model itself, and 
     * returning $model also
     * 
     * Example: At controller side, there is a method callback "setAttributesMethod($model)
     * 
     * public function setAttributesMethod($model)
     * {
     *     $model->attributes=$_POST['Model'];
     *     //do other logic
     *     //...
     *     return $model;
     * }
     * @var array
     */
    public $setAttributesMethod;
    /**
     * Run the action
     */
    public function run() 
    {
        if (!isset($this->loadModelMethod))
            $model = $this->controller->loadModel($_GET[$this->loadModelAttribute]);
        else {
            if (isset($this->loadModelAttribute))
                $model = $this->controller->{$this->loadModelMethod}($_GET[$this->loadModelAttribute]); 
            else
                $model = $this->controller->{$this->loadModelMethod}(); 
        }

        if ($this->controller->module->getServiceManager($this->serviceInvokeParam)===null)
            throw new CHttpException(500,Sii::t('sii','Service not found'));        
            
        if ($this->controller->module->getServiceManager($this->serviceInvokeParam)->checkObjectAccess(user()->getId(),$model,$this->serviceOwnerAttribute)){

            $this->controller->setPageTitle($this->getPageTitle($model));
        
            if(isset($_POST[$this->model])){
            
                try {
                    if (!isset($this->setAttributesMethod))
                        $model->attributes = $_POST[$this->model];
                    else 
                        $model = $this->controller->{$this->setAttributesMethod}($model);

                    $skipCheckAccess = false;//since this is already done at above lines
                    $this->invokeService(array(user()->getId(),$model,$skipCheckAccess));
                    
                 } catch (CException $e) {
                    $this->setErrorFlash($model,$e);
                }
            }           
       
            $this->renderPage($model);
        
        }
        else {
            logError('Unauthorized access', $model->getAttributes());
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
            $this->flashTitle = Sii::t('sii','{model} Update');
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
        return Sii::t('sii','Update {model}',array('{model}'=>$model->displayName()));
    } 
}
