<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.tasks.actions.TransitionAction");
Yii::import('common.components.actions.CRUDBaseAction');
Yii::import('common.components.actions.api.ApiActionTrait');
Yii::import('common.components.actions.api.ApiActionInterface');

/**
 * Description of ApiWorkflowAction
 *
 * @author kwlok
 */
class ApiWorkflowAction extends CRUDBaseAction implements ApiActionInterface 
{
    use ApiActionTrait {
        init as traitInit;
    }
    public $verb = 'POST';
    public $model;
    public $transitionAction;
    public $transitionConditionMap = [
        'condition1'=>'condition1',
        'condition2'=>'condition2',
    ];
    private $_transitionModel;
    /**
     * Init trait
     */
    public function init()
    {
        $this->httpVerb = $this->verb;
        $this->httpPostField = true;
        $this->traitInit();
        if (!isset($this->transitionAction))
            throw new CHttpException(500,Sii::t('sii','Transition action not defined'));    
    }  
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        $this->setApiModel(new $this->model);
        if ($this->apiModel->hasAttribute('account_id'))
            $this->apiModel->account_id = $this->user;
            
        if (isset($_POST['Transition'])){

            $this->transitionModel->attributes = $_POST['Transition'];
            logTrace(__METHOD__.' transition attributes', $this->transitionModel->getAttributes());
            $this->apiModel->id = $this->transitionModel->obj_id;

            $this->queryParams = '/'.$this->transitionAction.'/'.$this->apiModel->id;
            //set raw body fields
            $postFields = ['decision'=>$this->transitionModel->decision];
            foreach ($this->transitionConditionMap as $key => $value) {
                $postFields[$value] = $this->transitionModel->$key;
            }
            $this->postFields = json_encode($postFields);
            $this->findAccessToken();
            $this->execCurl($this->getAuthBearerHeader());                    
        }
    }
    
    public function onSuccess($response,$httpCode)
    {
        $this->refreshApiModel($response,['id','status']);

        $message = Sii::t('sii','{object} is set to {status} successfully',array('{object}'=>$this->apiModel->displayName(),'{status}'=>$this->apiModel->status));
        user()->setFlash(get_class($this->apiModel),array(
                    'message'=>$message,
                    'type'=>'success',
                    'title'=>Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($this->transitionAction)),'{model}'=>$this->apiModel->displayName()))));

        logTrace(__METHOD__.' ok');

        //above message is not captured after 'redirect' below
        header('Content-type: application/json');
        echo CJSON::encode(array(
            'status'=>WorkflowManager::SUCCESS, 
            'redirect'=>user()->hasRole(Role::ADMINISTRATOR)||user()->isSuperuser?request()->getUrlReferrer():$this->apiModel->viewUrl ,
        ));
        Yii::app()->end();
    }    
    
    public function onError($error, $httpCode) 
    {
        $this->setResponseErrorFlash($error, $httpCode);   
        $this->apiModel->status = WorkflowManager::getProcessBeforeAction(SActiveRecord::restoreTablename($this->model), $this->transitionAction);
        header('Content-type: application/json');
        echo CJSON::encode(array(
            'status'=>WorkflowManager::FAILURE, 
            'flash'=> $this->controller->sflashWidget(get_class($this->apiModel), true),
            'form'=>$this->controller->renderPartial($this->controller->module->getView('transitionform'),array(
                'model'=>$this->apiModel,
                'transition'=>$this->transitionModel,
                'action'=>$this->transitionAction,
                'decision'=>isset($this->transitionModel->decision)?$this->transitionModel->decision:WorkflowManager::DECISION_NULL),true),
        ));
        Yii::app()->end();        
    }
    
    protected function getTransitionModel()
    {
        if (!isset($this->_transitionModel)){
            $this->_transitionModel = new Transition();
        }
        return $this->_transitionModel;
    }
    
}
