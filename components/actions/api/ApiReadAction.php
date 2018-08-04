<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.ReadAction");
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');
/**
 * Description of ApiReadAction
 *
 * @author kwlok
 */
class ApiReadAction extends ReadAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $verb = 'GET';
    public $attributes = [];
    public $childAttributes = [];
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = $this->verb;
        $this->traitInit();
    }   
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        logInfo('['.$this->controller->uniqueId.'/'.$this->controller->action->id.'] '.__METHOD__.' $_GET', $_GET);
        if (isset($this->loadModelMethod)){
            $this->setApiModel($this->controller->{$this->loadModelMethod}());        
        }
        else {
            $this->findAccessToken();
            $this->queryParams = '/'.current(array_keys($_GET));//take the first key as search attribute
            $this->execCurl($this->getAuthBearerHeader());
        }
    } 
    
    public function onSuccess($response,$httpCode)
    {
        $this->setApiModel(new $this->model);
        if ($this->apiModel->hasAttribute('account_id'))
            $this->apiModel->account_id = $this->user;
        
        $this->controller->pageTitle = $this->getPageTitle($this->apiModel);

        if (isset($this->beforeRender))
            $this->controller->{$this->beforeRender}($this->apiModel);
            
        $this->refreshApiModel($response,$this->attributes,$this->childAttributes);
        $this->renderPage($this->apiModel);
    }
    
    public function onError($error, $httpCode) 
    {
        $this->setResponseErrorFlash($error, $httpCode);
    }
    
}
