<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.exceptions.*");
Yii::import("common.services.ServiceManagerTrait");
/**
 * Description of ServiceManager
 *
 * @author kwlok
 */
class ServiceManager extends CApplicationComponent 
{
    use ServiceManagerTrait;
    
    const WORKFLOW        = 'workflow';
    const WORKFLOW_BATCH  = 'workflow_batch';
    const ROLLBACK        = 'rollback';
    const NOTIFICATION    = 'notification';
    const PAYMENT         = 'payment';
    const ELASTICSEARCH   = 'elasticsearch';
    const EMPTY_PARAMS    = null;
    const NO_VALIDATION   = 'skip';
    const NO_NOTIFICATION = false;
    /**
     * Model(s) allowed in the service
     * @var mixed Single model type or array of models
     */
    public $model;
    
    public $ownerAttribute = 'account_id';
    /*
     * Run in "Api" mode will affect how ServiceException is returning error; Default to "local"
     */
    public $runMode = 'local';
    /*
     * If to throw error in html format
     */
    public $htmlError = false;
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Check if session user has access to model object
     * 
     * @param integer $user
     * @param CActiveRecord $model
     * @param mixed $attribute Array or string. The attribute of model used to check access rights, 
     * @return boolean
     */
    public function checkObjectAccess($user,$model,$attribute=null)
    {
        if (!isset($user))
            $user = Account::GUEST;
        
        if ($attribute===null)
            $attribute = $this->ownerAttribute;
        
        if (is_array($attribute)){
            $access = false;
            foreach ($attribute as $value) {
                if ($this->_checkAccess($user, $model, $value)){
                    $access = true;
                    break;
                }
            }
            return $access;
        }
        else {
            return $this->_checkAccess($user, $model, $attribute);
        }
    } 
    private function _checkAccess($user,$model,$attribute)
    {
        $sessionUser = $user;
        if (Account::isSubAccount($model->getAccountOwner()))
            $sessionUser = Account::decodeId($user);

        logTrace(__METHOD__.'('.$sessionUser.','.$model->getAccountOwner()->{$attribute}.') based on attribute '.$attribute);
        return Yii::app()->getAuthManager()->checkAccess(Task::MY_OBJECT,$user,[
            'session_user'=>$sessionUser,
            'model_owner'=>$model->getAccountOwner()->{$attribute},
        ]);
    }
    /**
     * Transition a model (activate or deactivate, bi-directional)
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to activate
     * @param string $action Indicate which action to perform (activate or deactivate)
     * @param string $iconReference Indicate which icon url reference to use; Mainly for question activation/deactivation
     * @return CModel $model
     * @throws CException
     */
    public function transition($user, $model, $action, $iconReference=null, $recordActivity=true,$notification=true, $checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        
        if (ucfirst($action)!=WorkflowManager::getActionAfterProcess($model->tableName(), $model->status)){
            logError(__METHOD__.' Invalid transition status for action = '.$action,$model->getAttributes());
            throw new ServiceValidationException(Sii::t('sii','Invalid transition status'));
        }
        
        $operations = [
            self::WORKFLOW=>[
                'transitionBy'=>$user,
                'condition'=>'Executed by user : '.$user,
                'action'=>$action,
                'decision'=>WorkflowManager::DECISION_YES,
                'saveTransition'=>true,
            ],
            'recordActivity'=>[
                'event'=>$action,
                'account'=>$user,
                'icon_url'=>$model->getActivityIconUrl($iconReference),
                'description'=>$model->getActivityDescription(),
            ],
            self::ELASTICSEARCH=>'saveSearchIndex',//refer to SearchableBehavior
        ];
        if (!$recordActivity)
            unset($operations['recordActivity']);
        
        return $this->execute($model, $operations, $model->getScenario(),$notification);                   
    }        
    /**
     * Execute the service 
     * 
     * @param type $model
     * @param type $operations Series of operations to perform for this service; 
     *                   Order of tasks is the sequence of execution
     * @param type $scenario Validation scenaior; Default to null (validate all rules)
     * @param type $notification Check how notification is send; Default to "true" (trigger notifications)
     * @return CModel $model
     * @throws CException
     */
    public function execute($model,$operations=[],$scenario=null,$notification=true)
    {
        $model->setScenario($scenario);
        if ($scenario!=self::NO_VALIDATION){
            if (!$model->validate() || $model->hasErrors()) {
                logError(__METHOD__.' scenario '.$scenario.' error',$model->getErrors());
                $this->throwValidationErrors($model->getErrors());
            }
        }
        else 
            logTrace(__METHOD__.' skip validation');
        
        $transaction = Yii::app()->db->beginTransaction();
        
        try {

            foreach ($operations as $operation => $params){
                logInfo(__METHOD__.' operation['.get_class($model).'.'.$operation.'] start');
                switch ($operation) {
                    case self::WORKFLOW:
                        $this->_validateWorkflowParams($params);
                        $transition = $this->getWorkflowManager()->transitionModel(
                                        $model,
                                        $params['transitionBy'],
                                        $params['condition'],
                                        $params['action'],
                                        $params['decision'],
                                        $params['saveTransition']);
                        if ($notification)
                            $this->execute($transition, 
                                   [self::NOTIFICATION=>self::EMPTY_PARAMS],
                                   self::NO_VALIDATION);
                        break;
                    case self::WORKFLOW_BATCH:
                        $this->_validateWorkflowParams($params);
                        $this->getWorkflowManager()->transitionBulkModels(
                                $params['models'],
                                $params['transitionBy'],
                                $params['condition'],
                                $params['action'],
                                $params['decision'],
                                $params['saveTransition']);
                        //WORKFLOW_BATCH now does not support notification sending.
                        //@todo need a new event sendBulk() at NotificationManager
                        break;
                    case self::ROLLBACK:
                        $this->_validateWorkflowParams($params,true);
                        $transition = $this->getWorkflowManager()->rollbackModel(
                                $model,
                                $params['transitionBy'],  
                                $params['saveTransition']);
                        if ($notification)
                            $this->execute($transition, 
                                   [self::NOTIFICATION=>self::EMPTY_PARAMS],
                                   self::NO_VALIDATION);
                        break;
                    case self::NOTIFICATION:
                        $this->getNotificationManager()->send($params==self::EMPTY_PARAMS?$model:$params);
                        break;
                    case self::PAYMENT:
                        $paymentManager = $this->getPaymentManager();
                        $this->_validatePaymentParams($params);
                        $paymentForm = $paymentManager->preparePaymentData($this->_createPaymentData($params['paymentData']));
                        $paymentManager->{$params['service']}($paymentForm); 
                        $operation = $params['service'];//change operation for clearer logging purpose
                        break;
                    case self::ELASTICSEARCH:
                        try {
                            if (is_callable([$model,$params], true, $callable_name)){
                                $model->$params();
                                logTrace(__METHOD__.' '.$callable_name.'() invoked');
                            }
                            else
                                logTrace(__METHOD__.' '.$params.'() behavior not attached => skipped.');
                                
                        } catch (CException $e) {
                            logInfo(__METHOD__.' '.$e->getMessage());
                        }
                        break;
                    default:
                        $model->{$operation}($params);
                        break;
                }
                logInfo(__METHOD__.' operation['.get_class($model).'.'.$operation.'] end');
            }
            
            $transaction->commit();
            
            logInfo(__METHOD__.' ok');
        
            return $model;

        } catch (CException $e) {
            logError(__METHOD__.' rollback: '.$e->getMessage().' >> '.$e->getTraceAsString(),$model->getAttributes(),false);
            $transaction->rollback();
            throw new ServiceOperationException($e->getMessage());
        }        
            
    }
    /**
     * Run default validation 
     * @param type $user
     * @param CModel $model
     * @param type $checkAccess
     * @throws InvalidArgumentException
     * @throws CException
     */
    protected function validate($user,$model,$checkAccess=true)
    {       
        if ($this->_validateModel($model)){
            logError(__METHOD__.' Invalid service model: '.get_class($model),$model->getAttributes());
            throw new CException(Sii::t('sii','Invalid service model'));
        }
        
        if ($checkAccess){
            if (!$this->checkObjectAccess($user, $model)){
                logError(__METHOD__.' Unauthorized access',$model->getAttributes());
                throw new CException(Sii::t('sii','Unauthorized Access to model'));
            }
        }        
    }
    /**
     * Validate workflow params
     */
    private function _validateWorkflowParams($params,$rollback=false)
    {
        if (!is_array($params))
            throw new ServiceValidationException(Sii::t('sii','Parameters must be in array format'));   
        
        $mandatoryFields = ['saveTransition','transitionBy'];
        if (!$rollback)
            $mandatoryFields = array_merge(['condition','decision'],$mandatoryFields);
        foreach ($mandatoryFields as $field) {
            //validate mandatory fields
            if (!array_key_exists($field, $params))
                throw new ServiceValidationException(Sii::t('sii','Missing parameter "{field}"',['{field}'=>$field]));   
        }
    }
    /**
     * Validate payment params
     */
    private function _validatePaymentParams($params)
    {
        if (!is_array($params))
            throw new ServiceValidationException(Sii::t('sii','Parameters must be in array format'));   
        
        $mandatoryFields = ['service','paymentData'];
        foreach ($mandatoryFields as $field) {
            //validate mandatory fields
            if (!array_key_exists($field, $params))
                throw new ServiceValidationException(Sii::t('sii','Missing parameter "{field}"',['{field}'=>$field]));   
        }
    }
    /**
     * Validate workflow models
     */
    public function validateModels($user,$models,$checkAccess=true)
    {
        if (!is_array($models))
             throw new ServiceValidationException(Sii::t('sii','Parameters must be in array format'));   
        
        foreach ($models as $model) {
            $this->validate($user,$model,$checkAccess);
        }
    }
    private function _validateModel($model)
    {
        $error = false;
        if (is_array($this->model)){
            $count = 0;
            foreach ($this->model as $allowedModel){
                if (!($model instanceof $allowedModel))
                    $count++;
            }
            if ($count==count($this->model))
                $error = true;
        }
        else {
            if (!($model instanceof $this->model))
                $error = true;
        }
        return $error;
    }
    private function _createPaymentData($params=[]) 
    {
        $paymentData = new stdClass();
        foreach ($params as $key => $value) {
            $paymentData->{$key} = $value;
        }
        return $paymentData;
    }    
    /**
     * Run default validation of worflow object
     * @param CModel $model
     * @param CModel $transition
     * @param string $scenario
     * @throws InvalidArgumentException
     * @throws CException
     */
    protected function validateWorkflow($model,$transition,$scenario,$htmlErrorOutput=true)
    {       
         if (!($transition instanceof Transition))
            throw new ServiceValidationException(Sii::t('sii','Invalid transition model'));
        
        $transition->setScenario($scenario);
        $transition->obj_type = $model->tableName();//for purpose to pick up correct label when validation has error
        if (!$transition->validate()) {
            logError(__METHOD__.' Transition validation error for scenario '.$scenario,$transition->getErrors());
            $this->throwValidationErrors($transition->getErrors(),$htmlErrorOutput);
        }
    }    
    /**
     * Boilerplate to run workflow (merchant side)
     * 
     * @param type $model
     * @param type $transition
     * @param type $scenario
     * @param type $event
     * @param type $validStatus
     * @return type
     * @throws CException
     */
    protected function runWorkflow($user,$model,$transition,$scenario,$event,$validStatus,$htmlErrorOutput=true)
    {
        $this->validate($user, $model, true);
        
        $this->validateWorkflow($model,$transition,$scenario,$htmlErrorOutput);
        
        $execute = false;
        if (is_array($validStatus)){
            if ($model->{$validStatus['method']}($validStatus['param']))
                $execute = true;
        }
        else {
            if ($model->{$validStatus}())
                $execute = true;
        }
        
        if ($execute){
            return $this->execute($model, [
                self::WORKFLOW=>[
                    'transitionBy'=>$user,
                    'condition'=> [
                        Transition::MESSAGE => [
                            $model->getCondition1Label($transition->decision)=>$transition->condition1,
                            $model->getCondition2Label($transition->decision)=>$transition->condition2,
                        ],
                    ],
                    'action'=>$transition->action,
                    'decision'=>$transition->decision,
                    'saveTransition'=>true,
                ],
                'recordActivity'=>[
                    'event'=>$event,
                ]
            ]);                   
        }
        else {
            logError(__METHOD__.' Invalid model status',$model->getAttributes());
            throw new ServiceValidationException(Sii::t('sii','Invalid model status'));
        }        
    }   
    /**
     * Throw correspnidng validation errors according to run mode
     * @see $runMode
     * 
     * @param type $errors
     * @throws ServiceValidationException
     */
    protected function throwValidationErrors($errors,$htmlOutput=false)
    {
        if ($this->runMode=="api")
            throw new ServiceValidationException(json_encode($errors));
        elseif ($htmlOutput||$this->htmlError)
            throw new ServiceValidationException(Helper::htmlErrors($errors));
        else
            throw new ServiceValidationException(Helper::implodeErrors($errors));
    }
    /**
     * This will run model as adminstrator, and change the account_id to admin user
     * @param type $model
     * @param type $adminUser
     * @return type
     */
    protected function runModelAsAdministrator($model,$adminUser)
    {
        $model->setOfficer($adminUser);
        $model->setAccountOwner('officerAccount');
        $this->ownerAttribute = 'id';
        return $model;
    }
    /**
     * Select the module that the service model belongs to
     * @param string $modelClass
     * @return type
     */
    public function parseModule($modelClass)
    {
        switch ($modelClass) {
            case 'ShippingOrder':
                $module = 'orders';
                break;
            case 'CampaignSale':
            case 'CampaignBga':
            case 'CampaignPromocode':
                $module = 'campaigns';
                break;
            case 'PaymentMethod':
                $module = 'payments';
                break;
            case 'Tax':
                $module = 'taxes';
                break;
            case 'Package':
                $module = 'plans';
                break;
            case 'TutorialSeries':
                $module = 'tutorials';
                break;
            case 'Media':
                $module = 'media';
                break;
            default:
                $module = SActiveRecord::plural(strtolower($modelClass));
                break;
        }
        return $module;        
    }   
}
