<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.components.actions.CreateAction');
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');
/**
 * Description of ApiCreateAction
 *
 * @author kwlok
 */
class ApiCreateAction extends CreateAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $verb = 'POST';
    /**
     * The call back function before render
     * @var type 
     */
    public $beforeRender;
    /*
     * The flash id to used on success event; Defaul to null <- using $this->model
     */
    public $flashIdOnSuccess;
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = $this->verb;
        $this->httpPostField = true;
        $this->traitInit();
    }    
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        if (isset($this->createModelMethod))
            $this->setApiModel($this->controller->{$this->createModelMethod}());
        else
            $this->setApiModel(new $this->model);

        $this->controller->pageTitle = $this->getPageTitle($this->apiModel);
        
        if(isset($_POST[$this->model])){

            try {
                        
                if (isset($this->setAttributesMethod))
                    $this->setApiModel($this->controller->{$this->setAttributesMethod}($this->apiModel));
                else 
                    $this->apiModel->attributes = $_POST[$this->model];

                logTrace(__METHOD__.' attributes',$this->apiModel->attributes);
                
                $this->findAccessToken();
                $this->execCurl($this->getAuthBearerHeader());
                
            } catch (CException $e) {
                
                $this->setErrorFlash($this->apiModel,$e);
            }
        }
        
        if (isset($this->beforeRender))
            $this->setApiModel($this->controller->{$this->beforeRender}($this->apiModel));
            
        $this->renderPage($this->apiModel);
        
    }     
    
    public function onSuccess($response,$httpCode)
    {
        if (isset($this->flashIdOnSuccess))
            $this->flashId = $this->flashIdOnSuccess;
        
        $this->setSuccessFlash($this->apiModel);
        unset($_POST);
        $this->refreshApiModel($response,['id']);
        $this->controller->redirect($this->getRedirectUrl($this->apiModel));
        Yii::app()->end();
    }

    public function onError($error, $httpCode) 
    {
        $this->setResponseErrorFlash($error, $httpCode);
    }

}
