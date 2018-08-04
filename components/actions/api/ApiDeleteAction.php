<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.DeleteAction");
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');
/**
 * Description of ApiDeleteAction
 *
 * @author kwlok
 */
class ApiDeleteAction extends DeleteAction implements ApiActionInterface
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $verb = 'DELETE';

    public function init() 
    {
        $this->httpVerb = $this->verb;
        $this->traitInit();
    }
    
    public function callApi() 
    {
        $this->findModel($_GET['id']);

        try {

            if (isset($this->beforeDelete))
                $this->controller->{$this->beforeDelete}($this->apiModel);

            $this->findAccessToken();
            $this->execCurl($this->getAuthBearerHeader()); 
        
        } catch (CException $e) {
            $this->setErrorFlash($this->apiModel,$e);
            $this->controller->redirect($this->apiModel->viewUrl);
        }
            
    }

    public function onSuccess($response, $httpCode) 
    {
        if ($this->httpVerb=='GET')
            $this->refreshApiModel($response,['id']);
        if ($httpCode==204){
            $this->setSuccessFlash($this->apiModel);
            $this->controller->redirect($this->redirectUrl);
        }
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
            'httpVerb',
        ]);
        //change setting for read
        $this->httpVerb = 'GET';
        $this->findAccessToken();
        $this->execCurl($this->getAuthBearerHeader()); 
        //restore back default settings
        $this->restoreParams();
        $this->resetCurl();
    }
}
