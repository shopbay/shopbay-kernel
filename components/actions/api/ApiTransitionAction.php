<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.tasks.actions.TransitionAction");
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');

/**
 * Description of ApiTransitionAction
 *
 * @author kwlok
 */
class ApiTransitionAction extends TransitionAction implements ApiActionInterface 
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $verb = 'POST';
    public $model;
    public $transitionAction;
    public $attributes = [];
    public $childAttributes = [];
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = $this->verb;
        $this->traitInit();
        if (!isset($this->transitionAction))
            throw new CHttpException(500,Sii::t('sii','Transition action not defined'));    
    }  
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        if (isset($_REQUEST[$this->model][$this->keyAttribute])){

            if (Yii::app()->request->getIsPostRequest()){

                try {
                    
                    $this->setApiModel(new $this->model);
                    if ($this->apiModel->hasAttribute('account_id'))
                        $this->apiModel->account_id = $this->user;
                    $this->apiModel->id = $_REQUEST[$this->model][$this->keyAttribute];

                    $this->queryParams = '/'.$this->transitionAction.'/'.$this->apiModel->id;

                    $this->findAccessToken();
                    $this->execCurl($this->getAuthBearerHeader());                    

                 } catch (CException $e) {
                    logError($e->getMessage(),array(),false);
                    user()->setFlash(get_class($this->apiModel),array('message'=>$e->getMessage(),
                                   'type'=>$this->errorType,
                                   'title'=>$this->errorTitle));
                    $this->getController()->render($this->errorViewFile, array_merge(array('model'=>$this->apiModel),eval($this->viewData)));
                }
                
            }            
        }
    }
    
    public function onSuccess($response,$httpCode)
    {
        unset($_POST);
        $this->apiModel->setIsNewRecord(false);
        if ($this->apiModel->hasAttribute('account_id'))
            $this->apiModel->account_id = $this->user;
        $this->refreshApiModel($response,$this->attributes,$this->childAttributes);
        logTrace(__METHOD__,$this->apiModel->attributes);
        foreach ($this->childAttributes as $field) {
            logTrace(__METHOD__.' child '.$field,$this->apiModel->$field);
        }
        user()->setFlash(get_class($this->apiModel),array('message'=>$this->getSuccessMessage($this->apiModel),
                       'type'=>'success',
                       'title'=>$this->flashTitle));
        $this->getController()->render($this->viewFile, array_merge(array('model'=>$this->apiModel),eval($this->viewData)));
    }    
    
    public function onError($error, $httpCode) 
    {
        $this->setResponseErrorFlash($error, $httpCode);
    }
    
}
