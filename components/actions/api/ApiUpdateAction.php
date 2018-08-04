<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.UpdateAction");
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');
/**
 * Description of ApiUpdateAction
 *
 * @author kwlok
 */
class ApiUpdateAction extends UpdateAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $verb = 'PUT';
    public $inputRawBody = true;
    public $attributes = [];
    public $childAttributes = [];
    /**
     * The call back function before render
     * @var type 
     */
    public $beforeRender;
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = $this->verb;
        $this->httpPostField = $this->inputRawBody;
        $this->traitInit();
    }  
    
    public function callApi() 
    {
        if (!isset($this->loadModelMethod))
            $this->findModel($_GET[$this->loadModelAttribute]);
        else {
            if (isset($this->loadModelAttribute))
                $this->setApiModel($this->controller->{$this->loadModelMethod}($_GET[$this->loadModelAttribute]));
            else
                $this->setApiModel($this->controller->{$this->loadModelMethod}());
        }

        $this->controller->pageTitle = $this->getPageTitle($this->apiModel);

        if(isset($_POST[$this->model])){

            try {
                
                if (isset($this->setAttributesMethod))
                    $this->setApiModel($this->controller->{$this->setAttributesMethod}($this->apiModel));
                else 
                    $this->apiModel->attributes = $_POST[$this->model];

                $this->findAccessToken();
                $this->execCurl($this->getAuthBearerHeader());

             } catch (CException $e) {
                 
                $this->setErrorFlash($this->apiModel,$e);
            }
        }           

        $this->renderPage($this->apiModel);
        
    }
    /**
     * Shared between read (findModel) and update
     * @param type $response
     * @param type $httpCode
     */
    public function onSuccess($response, $httpCode) 
    {
        if ($this->httpVerb=='PUT'){
            $this->setSuccessFlash($this->apiModel);
            unset($_POST);
        }

        $this->setApiModel(new $this->model);
        if ($this->apiModel->hasAttribute('account_id'))
            $this->apiModel->account_id = $this->user;
        $this->apiModel->setIsNewRecord(false);
        $this->refreshApiModel($response,$this->attributes,$this->childAttributes);
        logTrace(__METHOD__,$this->apiModel->attributes);
        foreach ($this->childAttributes as $field) {
            logTrace(__METHOD__.' child '.$field,$this->apiModel->$field);
        }
        if (isset($this->beforeRender))
            $this->setApiModel($this->controller->{$this->beforeRender}($this->apiModel));
    }
    
    public function onError($error, $httpCode) 
    {
        $this->setResponseErrorFlash($error, $httpCode);
    }
    
    /**
     * Find the model by sending api read endpoint
     * @param type $id
     */
    public function findModel($id)
    {
        $this->queryParams = '/'.$id;
        $this->setApiModel(new $this->model);
        if ($this->apiModel->hasAttribute('account_id'))
            $this->apiModel->account_id = $this->user;
        //change setting for read
        $this->backupParams([
            'httpVerb','httpPostField',
        ]);
        $this->httpVerb = 'GET';
        $this->httpPostField = false;
        $this->findAccessToken();
        $this->execCurl($this->getAuthBearerHeader());
        //restore back default settings
        $this->restoreParams();
        $this->resetCurl();
    }

}
