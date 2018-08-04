<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of CRUDBaseAction
 *
 * @author kwlok
 */
class CRUDBaseAction extends CAction 
{
    /**
     * The service name to invoke in ServiceManager. Defaults to 'null'
     * @var string
     */
    public $service;
    /**
     * The param used to invoke ServiceManager. Defaults to 'null'
     * @var string
     */
    public $serviceInvokeParam;    
    /**
     * The service owner attribute to invoke ServiceManager. Defaults to 'null'
     * @var string
     */
    public $serviceOwnerAttribute;    
    /**
     * Class of the model to perform CRUD. Defaults to 'null'
     * @var string
     */
    public $model;
    /**
     * The page title to display
     * @var type
     */
    public $htmlPageTitle;
    /**
     * Name of the name attribute to load model type. Defaults to 'null'
     * @var string
     */
    public $nameAttribute;
    /**
     * Id of flash message. If not set, defaults to model name "$model"
     * @var string
     */
    public $flashId;
    /**
     * Title of flash message. Defaults to below
     * @var string
     */
    public $flashTitle;
    /**
     * Message body of flash message. Defaults to below
     * @var string
     */
    public $flashMessage;
    /**
     * View file name. Defaults to 'null'
     * @var string
     */
    public $viewFile;
    /**
     * Redirect url upon success. Defaults to null
     * @var string
     */
    public $redirectUrl;
    /**
     * Redirect url upon success if $redirectUrl not set. Defaults to null, meaning '$model->viewUrl'
     * @var string
     */
    public $viewUrl = 'viewUrl';
    /**
     * A callback method to set extra service params on top of action initial params
     * Example: At controller side, there is a method callback "setExtraParamsMethod()
     * 
     * public function setExtraParamsMethod()
     * {
     *     //do some logic
     *     //...
     *     return array('param1'=>'yes','param2'=>'no');
     * }
     * @return array
     */
    public $setExtraParamsMethod;    
    /**
     * A placeholder for extra service params. Defaults to null
     * @var string
     */
    public $extraParams;    
    /**
     * Generate page title
     * 
     * @param type $model
     * @return type
     */
    protected function getPageTitle($model)
    {
        return ucfirst($this->id).' '.$model->displayName();
    }
    /**
     * Render page
     * 
     * @param type $model
     * @return type
     */
    protected function renderPage($model)
    {
        $this->controller->render($this->viewFile,['model'=>$model]);
    }
    /**
     * Generate success flash
     * 
     * @param type $model
     * @return type
     */
    protected function setSuccessFlash($model)
    {
        user()->setFlash(isset($this->flashId)?$this->flashId:get_class($model),[
            'message'=>str_replace('{name}',isset($this->nameAttribute)?$model->{$this->nameAttribute}:$model->displayName(),$this->flashMessage),
            'type'=>'success',
            'title'=>str_replace('{model}',$model->displayName(),$this->flashTitle),
        ]);
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
            $this->flashTitle = Sii::t('sii','{model} Error');
        
        logError(__METHOD__.' '.$exception->getTraceAsString(), [], false);

        user()->setFlash(isset($this->flashId)?$this->flashId:get_class($model),[
            'message'=>$model->hasErrors()?Helper::htmlErrors($model->getErrors()):$exception->getMessage(),
            'type'=>'error',
            'title'=>str_replace('{model}',$model->displayName(),$this->flashTitle),
        ]);
    }
    /**
     * Invoke service 
     */
    protected function invokeService($params)
    {
        if ($this->controller->module->getServiceManager($this->serviceInvokeParam)===null)
            throw new CHttpException(500,Sii::t('sii','Service provider not found'));        
                
        if (!isset($this->service))
            throw new CHttpException(500,Sii::t('sii','Service not defined'));        
        
        if (isset($this->setExtraParamsMethod))
            $this->extraParams = $this->controller->{$this->setExtraParamsMethod}();

        if (isset($this->extraParams))
            $params = array_merge($params, $this->extraParams);
        
        $model = call_user_func_array([$this->controller->module->getServiceManager($this->serviceInvokeParam),$this->service],$params);
        $this->setSuccessFlash($model);
        unset($_POST);
        $this->controller->redirect($this->getRedirectUrl($model));
        Yii::app()->end();
    }
    
    protected function getRedirectUrl($model)
    {
        if (isset($this->redirectUrl))
            return $this->redirectUrl;
        else
            return $model->{$this->viewUrl};
    }
    
}
