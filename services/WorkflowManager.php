<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.tasks.models.*");
Yii::import("common.services.workflow.models.Transitionable");
Yii::import("common.services.workflow.events.*");
Yii::import("common.services.exceptions.*");
/**
 * Description of Workflow manager
 *
 * @author kwlok
 */
class WorkflowManager extends CApplicationComponent 
{
    const ITEM_1STEP        = '1step';
    const SUCCESS           = 'success';
    const FAILURE           = 'failure';
    const DECISION_NULL     = null;
    const DECISION_YES      = 'Yes';
    const DECISION_NO       = 'No';
    const DECISION_ACCEPT   = 'Accept';
    const DECISION_REJECT   = 'Reject';
    const DECISION_ORDER    = 'Order';
    const DECISION_PAY      = 'Pay';
    const DECISION_PARTIAL  = 'Partial';//partial fulfill
    const DECISION_HOLD     = 'Hold';//onhold payment
    const DECISION_DEFER    = 'Defer';//defer payment
    const DECISION_SHIP     = 'Ship';
    const DECISION_CANCEL   = 'Cancel';
    const DECISION_REPAY    = 'Repay';
    const DECISION_RECEIVE  = 'Receive';
    const DECISION_REVIEW   = 'Review';
    const DECISION_RETURN   = 'Return';
    const DECISION_FULFILL  = 'Fulfill';
    const DECISION_HASSTOCK = 'HasStock';
    const DECISION_NOSTOCK  = 'NoStock';
    const DECISION_COLLECT  = 'Collect';
    const ACTION_PURCHASE   = 'Purchase';
    const ACTION_DELIVER    = 'Deliver';
    const ACTION_CANCEL     = 'Cancel';
    const ACTION_PAY        = 'Pay';
    const ACTION_PROCESS    = 'Process';
    const ACTION_ROLLBACK   = 'Rollback';
    const ACTION_REFUND     = 'Refund';
    const ACTION_VERIFY     = 'Verify';
    const ACTION_PICK       = 'Pick';
    const ACTION_PACK       = 'Pack';
    const ACTION_SHIP       = 'Ship';
    const ACTION_RECEIVE    = 'Receive';
    const ACTION_REOPEN     = 'Reopen';
    const ACTION_REPAY      = 'Repay';
    const ACTION_REVIEW     = 'Review';
    const ACTION_RETURN     = 'Return';
    const ACTION_RETURNITEM = 'ReturnItem';//need this due to reseved word 'return' in php
    const ACTION_ACCEPT     = 'Accept';
    const ACTION_APPLY      = 'Apply';
    const ACTION_APPROVE    = 'Approve';
    const ACTION_ADJUST     = 'Adjust';
    const ACTION_ASK        = 'Ask';
    const ACTION_ANSWER     = 'Answer';
    const ACTION_ACTIVATE   = 'Activate';
    const ACTION_DEACTIVATE = 'Deactivate';
    const ACTION_DEPOSIT    = 'Deposit';
    const ACTION_WITHDRAW   = 'Withdraw';
    const ACTION_PUBLISH    = 'Publish';
    const ACTION_CHANGE     = 'Change';
    const ACTION_SUBMIT     = 'Submit';
    const ACTION_CLOSE      = 'Close';
    const ACTION_FULFILL    = 'Fulfill';
    const DECISION_SEPARATOR = '||';
          
    public function init() 
    {
        parent::init();    
        $this->attachEventHandler('onTransition',array($this,'transition'));
        $this->attachEventHandler('onRollback',array($this,'transition'));
        $this->attachEventHandler('onInterrupt',array($this,'interrupt'));
        logTrace('Workflow events attached');        
    }
    public static function getProcess($objType,$process,$action=null)
    {
        if (isset($action))
            $workflow= Workflow::model()->find('obj_type=:obj_type and start_process=:process and action=:action',[':obj_type'=>$objType,':process'=>$process,':action'=>$action]); 
        else
            $workflow= Workflow::model()->find('obj_type=:obj_type and start_process=:process',[':obj_type'=>$objType,':process'=>$process]); 
            
        return isset($workflow)?$workflow:null;
    }
    /**
     * Get process by Action (always return first found)
     * @param type $objType
     * @param type $action
     * @return type
     */
    public static function getProcessByAction($objType,$action)
    {
         $workflow= Workflow::model()->find('obj_type=:obj_type and action=:action',array(':obj_type'=>$objType,':action'=>$action)); 
         if ($workflow!=null)
            return $workflow;       
         return null;
    }
    
    public static function getPreviousProcess($objType,$currentProcess)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        $criteria->compare('end_process',$currentProcess,true,'AND',true);
        logTrace(__METHOD__.' criteria',$criteria);
        $workflow= Workflow::model()->find($criteria); 
        if ($workflow!=null)
            return $workflow->start_process;       

        return null;
    }
    public static function getNextProcess($objType,$currentProcess,$action=null,$decision=null)
    {
        if (isset($action))
            $workflow= Workflow::model()->find('obj_type=:obj_type and start_process=:process and action=:action',
                                            array(':obj_type'=>$objType,':action'=>$action,':process'=>$currentProcess)); 
        else
            $workflow= Workflow::model()->find('obj_type=:obj_type and start_process=:process',
                                            array(':obj_type'=>$objType,':process'=>$currentProcess)); 
        
        if ($workflow!=null)
            return $workflow->getEndProcess($decision);
        return null;
    }
    /**
     * Return process before action;
     * As workflow may have same name of action; If yes, return all start_process for the same action name
     * @param type $objType
     * @param type $action
     * @return mixed string or Array
     */
    public static function getProcessBeforeAction($objType,$action)
    {
        $workflow= Workflow::model()->findAll('obj_type=:obj_type and action=:action',
                                            array(':obj_type'=>$objType,':action'=>$action)); 
        if ($workflow!=null){
            if (count($workflow)>1){
                $processes = array();
                foreach ($workflow as $record) {
                    $processes[] = $record->start_process;
                }
                return $processes;
            }
            else
                return $workflow[0]->start_process;//one record in array
        }
        return null;
    }
    public static function getAllProcessesBeforeAction($objType,$action)
    {
        $criteria=new CDbCriteria;
        $criteria->select='start_process';
        $criteria->addColumnCondition(array('obj_type'=>$objType,'action'=>$action));
        $processes = new CList();
        foreach(Workflow::model()->findAll($criteria) as $process){
            $processes->add($process->start_process);
        }
        return $processes->toArray();
    }
    public static function getProcessAfterAction($objType,$action,$decision='default')
    {
        $workflow= Workflow::model()->find('obj_type=:obj_type and action=:action',array(':obj_type'=>$objType,':action'=>$action)); 
        if ($workflow!=null)
            return $workflow->getEndProcess($decision);
        return null;
    }    
    
    public static function getActionBeforeProcess($objType,$process)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        $criteria->compare('end_process',$process,true,'AND',true);
        logTrace(__METHOD__.' criteria',$criteria);
        $workflow= Workflow::model()->find($criteria); 
        if ($workflow!=null)
            return $workflow->action;   
        return null;
    }
    public static function getActionAfterProcess($objType,$process,$startBy=null)
    {
        if (isset($startBy))
            $workflow= Workflow::model()->find('obj_type=:obj_type and start_process=:process and start_by=:start_by',array(':obj_type'=>$objType,':process'=>$process,':start_by'=>$startBy)); 
        else
            $workflow= Workflow::model()->find('obj_type=:obj_type and start_process=:process',array(':obj_type'=>$objType,':process'=>$process)); 
        
        if ($workflow!=null)
            return $workflow->action;  
        
        return null;
    }
    public static function getDecisionAfterProcess($objType,$process, $action=null)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        $criteria->addColumnCondition(array('start_process'=>$process));
        if (isset($action))
            $criteria->addColumnCondition(array('action'=>$action));
        //logTrace(__METHOD__.' criteria',$criteria);
        $workflow= Workflow::model()->find($criteria); 
        if ($workflow!=null) {
            return $workflow->getDecision();
        }
        return array();//empty decision
    }
    public static function getDecisionBeforeProcess($objType,$process)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        $criteria->compare('end_process',$process,true,'AND',true);
        logTrace(__METHOD__.' criteria',$criteria);
        $workflow= Workflow::model()->find($criteria); 
        if ($workflow!=null) {
            return $workflow->getEndProcessDecision($process);
        }
        throw new CException(Sii::t('sii','Process not found'));
    }
    public static function getPotentialDecisions($objType,$process,$action)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        $criteria->addColumnCondition(array('start_process'=>$process));
        $criteria->addColumnCondition(array('action'=>$action));
        $workflow= Workflow::model()->find($criteria); 
        if ($workflow!=null){
            if ($workflow->decision==null)
                return null;
            else 
                return explode(self::DECISION_SEPARATOR, $workflow->decision);//multiple decisions has "||' in between, e.g. Pay||Hold
        }
        throw new CException(Sii::t('sii','Process not found'));
    }    
    public static function getNextAction($objType,$action)
    {
       return self::getActionAfterProcess($objType,self::getProcessAfterAction($objType, $action));
    }
    public static function getPreviousAction($objType,$action)
    {
       return self::getActionBeforeProcess($objType,self::getProcessBeforeAction($objType, $action));
    }
    
    public static function getEndProcessDecision($objType,$processFrom, $processTo)
    {
        $workflow= Workflow::model()->find('obj_type=:obj_type and start_process=:start_process',
                                            array(':obj_type'=>$objType,':start_process'=>$processFrom)); 
        if ($workflow!=null)
            return $workflow->getEndProcessDecision($processTo);   
        return null;
    }
    /**
     *  According to s_workflow definition, the first process has the lowest id for each object type
     */
    public static function beginProcess($objType)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        $criteria->order = 'id ASC';
        $workflow= Workflow::model()->find($criteria); 
        if ($workflow==null)
            throw new CException('Unknown object type');   
        return $workflow->start_process;
    }
    public static function getAllStartProcesses($objType,$excludes=array(),$startBy=null)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        if (isset($startBy))
            $criteria->addColumnCondition(array('start_by'=>$startBy));
        $criteria->order = 'id ASC';
        $workflows = Workflow::model()->findAll($criteria); 
        if ($workflows==null)
            throw new CException('Unknown object type');  
        $processes = new CMap();
        foreach ($workflows as $workflow) {
            if (!in_array($workflow->start_process,$excludes))
                $processes->add($workflow->start_process, strtolower(Process::getText($workflow->start_process)));
        }
        return $processes->toArray();
    }    
    public static function getAllEndProcesses($objType,$excludes=array(),$startBy=null)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$objType));
        if (isset($startBy))
            $criteria->addColumnCondition(array('start_by'=>$startBy));
        $criteria->order = 'id ASC';
        $workflows = Workflow::model()->findAll($criteria); 
        if ($workflows==null)
            throw new CException('Unknown object type');  
        $processes = new CMap();
        foreach ($workflows as $workflow) {
            $endProcess = $workflow->getAllEndProcess();
            if (is_array($endProcess)){
                foreach ($endProcess as $_process) {
                    if (!in_array($_process,$excludes))
                        $processes->add($_process, strtolower(Process::getText($_process)));
                }
            }
            else {
                if (!in_array($endProcess,$excludes))
                    $processes->add($endProcess, strtolower(Process::getText($endProcess)));
            }
        }
        return $processes->toArray();
    }   
    public static function getAllProcesses($objType,$excludes=array(),$startBy=null)
    {
        $startProcesses = self::getAllStartProcesses($objType,$excludes,$startBy);
        $endProcesses = self::getAllEndProcesses($objType,$excludes,$startBy);
        return $startProcesses + $endProcesses;//array union
    }    
    public static function getAllActions()
    {
        return 'TODO';//TODO
    } 
    public static function getProcessDataProvider($role,$objType,$exclusions=array())
    {
        if ($role==Role::CUSTOMER){
            $exclusions = array_merge([
                                Order::model()->tableName()=> array(WorkflowManager::ACTION_PURCHASE),
                                Item::model()->tableName()=> array(WorkflowManager::ACTION_PURCHASE,WorkflowManager::ACTION_PAY,WorkflowManager::ACTION_REPAY),
                                'start_process'=>array(Process::COLLECTED),
                            ],$exclusions);
        }
        if ($role==Role::MERCHANT){
            $exclusions =  array_merge([
                                Item::model()->tableName()=>array(WorkflowManager::ACTION_VERIFY),
                                Order::model()->tableName()=>array(WorkflowManager::ACTION_CANCEL,WorkflowManager::ACTION_PROCESS,WorkflowManager::ACTION_FULFILL,WorkflowManager::ACTION_DELIVER,WorkflowManager::ACTION_REFUND),
                                ShippingOrder::model()->tableName()=>array(WorkflowManager::ACTION_FULFILL),
                                Shop::model()->tableName()=>array(WorkflowManager::ACTION_APPLY,WorkflowManager::ACTION_CHANGE),
                                'start_process'=>array(Process::SHOP_APPROVED),
                            ],$exclusions);    
        }
        if ($role==Role::ADMINISTRATOR){
            $exclusions =  array_merge(array(
                TutorialSeries::model()->tableName()=>array(WorkflowManager::ACTION_SUBMIT)),
                $exclusions);
        }

        $criteria=new CDbCriteria(); 
        $criteria->addCondition('start_by=\''.$role.'\' and obj_type=\''.$objType.'\'');
        foreach($exclusions as $type => $excludes){
            if ($objType==$type)
                $criteria->addCondition(QueryHelper::constructNotInCondition('action',$excludes));
            if ($type=='start_process')
                $criteria->addCondition(QueryHelper::constructNotInCondition('start_process',$excludes));
        }
        return new CActiveDataProvider('Workflow', array('criteria'=>$criteria));
    }            
    /**
     * Raises an <code>onTransition</code> event.
     * @param TransitionEvent the event parameter
     */
    public  function onTransition($event)
    {
        $this->raiseEvent('onTransition', $event);
    }
    /**
     * Raises an <code>onRollback</code> event.
     * @param TransitionEvent the event parameter
     */
    public  function onRollback($event)
    {
        $this->raiseEvent('onRollback', $event);
    }
    /**
     * Raises an <code>onInterrupt</code> event.
     * @param TransitionEvent the event parameter
     */
    public  function onInterrupt($event)
    {
        $this->raiseEvent('onInterrupt', $event);
    }
    /**
     * Process the <code>onTransition</code> event.
     * 
     * @param TransitionEvent the event parameter
     * @return type 
     */
    public function transition($event)
    {
        if (!($event->transition instanceof Transition))
            throw new ServiceWorkflowException(Sii::t('sii','Invalid transition object'));

        if (!isset($event->transition->transition_by))
            throw new ServiceWorkflowException(Sii::t('sii','Transition user not found'));
        
        logTrace(__METHOD__.' transition model '.get_class($event->model),$event->model->getAttributes());
        
        logTrace(__METHOD__.' transition object',$event->transition->getAttributes());

        try {

            $event->model->runTransition($event->transition,$event->saveTransition);

            logInfo(__METHOD__.' '.$event->model->tableName().' '.$event->model->id.' ok');

        } catch (Exception $e) {
            logError(__METHOD__.' >> '.$e->getTraceAsString(), $event->transition->getAttributes());
            throw new ServiceWorkflowException($e->getMessage());
        }           
    } 
    /**
     * Transition by Model class
     * 
     * @param Transitionable $model
     * @param type $transitionBy
     * @param type $conditions
     * @param type $action
     * @param type $decision
     * @param type $saveTransition
     * @return type
     * @throws CException
     */
    public function transitionModel($model,$transitionBy,$conditions,$action,$decision=null,$saveTransition=true)
    {
         if (!($model instanceof Transitionable))
            throw new ServiceWorkflowException(Sii::t('sii','Invalid transition object'));
        
        if (!isset($transitionBy))
            throw new ServiceWorkflowException(Sii::t('sii','Transition user not found'));

        $transition = new Transition;
        $transition->obj_id = $model->id;
        $transition->obj_type = $model->tableName();
        $transition->process_from = $model->status;
        $transition->action = $action;
        $transition->decision = $decision;
        $transition->process_to = $model->getNextStatus($transition->action,$transition->decision);
        $transition->setConditions($conditions);
        $transition->transition_by = $transitionBy;
            
        if (Yii::app()->getAuthManager()->checkAccess($this->_getPermission($transition),$transitionBy)){
            $this->onTransition(new TransitionEvent($this,$model,$transition,$saveTransition));
            return $transition;
        }
        else {
            logError('No access '.$this->_getPermission($transition),$transition->getAttributes(),false);
            throw new ServiceWorkflowException(Sii::t('sii','Unauthorized Access'));
        }  
    }
    /**
     * Bulk Transition models, all share common conditions and decisions
     * 
     * @param Transition $transitionModel expect Transition class
     * @param type $transitionBy
     * @param type $conditions
     * @param type $action
     * @param type $decision
     * @param type $saveTransition
     * @return type
     * @throws CException
     */
    public function transitionBulkModels($models,$transitionBy,$conditions,$action,$decision=null,$saveTransition=true)
    {
        if (!is_array($models))
            throw new ServiceWorkflowException(Sii::t('sii','Invalid argument - input must be an array'));

        if (count($models) < 1)
            throw new ServiceWorkflowException(Sii::t('sii','Invalid argument - must contain at least one transition object'));
        
        if (!isset($transitionBy))
            throw new ServiceWorkflowException(Sii::t('sii','Transition user not found'));

        $transitions = new CMap();
        foreach ($models as $model) {
            
            if (!($model instanceof Transitionable))
                throw new CException(Sii::t('sii','Invalid transition object'));
         
            $transition = new Transition;
            $transition->obj_id = $model->id;
            $transition->obj_type = $model->tableName();
            $transition->process_from = $model->status;
            $transition->action = $action;
            $transition->decision = $decision;
            $transition->setConditions($conditions);
            $transition->process_to = $model->getNextStatus($transition->action,$transition->decision);
            $transition->transition_by = $transitionBy;
            logTrace(__METHOD__.' transistion object',$transition->getAttributes());
            $transitions->add($model->id,$transition);

        }
        
        //Assess first model to get access rights
        $firstTransition = $transitions->itemAt($models[0]->id);
        
        if (Yii::app()->getAuthManager()->checkAccess($this->_getPermission($firstTransition),$transitionBy)){
            foreach ($models as $model)
                $this->onTransition(new TransitionEvent($this,$model,$transitions->itemAt($model->id),$saveTransition));
            logInfo(__METHOD__.' bulk('.count($models).') '.$firstTransition->obj_type.' ok');
            return self::SUCCESS;
        }
        else {
            logError('No access: $firstTransition attributes',$firstTransition->getAttributes(),false);
            throw new ServiceWorkflowException(Sii::t('sii','Unauthorized Access'));
        }    
    }
    /**
     * Rollback Transition 
     * 
     * @param Transition $model expect Transition class
     * @param type $transitionBy
     * @param type $saveTransition
     * @return type
     * @throws CException
     */
    public function rollbackModel($model,$transitionBy,$saveTransition=true)
    {
        if (!$model->undoable())
            throw new ServiceWorkflowException(Sii::t('sii','Model rollbacked not allowed'));
        
        if (!isset($transitionBy))
            throw new ServiceWorkflowException(Sii::t('sii','Transition user not found'));

        $transition = new Transition;
        $transition->setRollback($model); 
        $transition->process_to = self::getPreviousProcess($transition->obj_type, $transition->process_from);
        $transition->transition_by = $transitionBy;
        
        if (Yii::app()->getAuthManager()->checkAccess($this->_getPermissionPrefix($transition->getObject()).'Rollback',$transitionBy) ||
            Yii::app()->getAuthManager()->checkAccess('Tasks.Workflow.Rollback',$transition->transition_by)){
            $this->onRollback(new TransitionEvent($this,$model,$transition,$saveTransition));
            return $transition;
        }
        else {
            logError('No access',$model->getAttributes(),false);
            throw new ServiceWorkflowException(Sii::t('sii','Unauthorized Access'));
        }
    }    
    /**
     * @TODO Interrupt normal Transition 
     * @param Transition $model expect Transition class
     * @param process The process interrupt to bring to
     * @return type 
     */
    public function interrupt($model,$jumpProcess)
    {        
        if (!($model instanceof Transition))
            throw new ServiceWorkflowException(Sii::t('sii','invalid argument - non-Transition object'));
        
        //First transition to Process::INTERRUPT
        $model->process_to = Process::INTERRUPT;
        
        logInfo('Start transition '.$model->obj_type.' '.$model->obj_id.' '.$model->process_from.' -> '.$model->process_to.' -> '.$jumpProcess);
        
        if (user()->checkAccess('Tasks.Workflow.Interrupt')){

            if($model->save()){
                    
                logTrace('Process Interrupted, proceed transition to '.$jumpProcess);

                //Second transition again according to $jumpProcess

                 $transition = new Transition();
                 $transition->obj_id = $model->obj_id;
                 $transition->obj_type = $model->obj_type;
                 $transition->process_from = $model->process_to;
                 $transition->process_to = Process::getNameByDesc($jumpProcess);
                 $transition->condition1 = 'auto-triggered by interrupt';

                 if (user()->checkAccess($this->_getPermissionPrefix($model).$jumpProcess)){

                    if($transition->save()){

                           $this->notification->send($transition);

                           logInfo('End transition '.$transition->obj_type);

                            return array(
                                'status'=>self::SUCCESS, 
                                'action'=> self::getActionBeforeProcess($transition->obj_type,$transition->process_to)
                            );
                    }
                    else {
                        logError('Fail to make transition',$transition->getErrors(),false);
                        return array(
                                'status'=>self::FAILURE, 
                                'error'=>$transition->getErrors());
                    }
                }
                else {
                    logError('No access to Task.'.$jumpProcess,array(),false);
                    throw new ServiceWorkflowException(Sii::t('sii','Unauthorized Access'));
                }
            }
            else {
                logError('Fail to interrupt transition',$model->getErrors(),false);
                return array(
                        'status'=>self::FAILURE, 
                        'error'=>$model->getErrors());
            }
        }
        else {
            logError('No access',$model->getAttributes(),false);
            throw new ServiceWorkflowException(Sii::t('sii','Unauthorized Access'));

        }
         
    }
    /**
     * Get permission
     * 
     * @param type $transition
     * @return string
     */
    private function _getPermission($transition)
    {
        $access = $this->_getPermissionPrefix($transition->getObject())
                  .self::getActionAfterProcess($transition->obj_type,$transition->process_from);
        logInfo('Checking access '.$access);  
        return $access;
    }
    /**
     * Return task permission prefix "[module].[controller]."
     * Apply to module 'Tasks' only.
     * 
     * The approach is a backward calcuation based on object_type
     * 
     * Example:
     * s_item => Tasks.Item.
     * s_order => Tasks.Order.
     * s_shipping_order => Tasks.ShippingOrder.
     * 
     * Note: 
     * 1. Full permission Format: [module].[controller].[action]
     * 2. Refer more to tasks/data/schema.sql
     * 3. Not apply to Activation/Deactivation tasks, such as s_shop, s_campaign_bga, s_payment_method
     * 
     * @param type $obj_type
     * @return String permission prefix 
     */
    private function _getPermissionPrefix($model)
    {
        return 'Tasks.'.get_class($model).'.';
    }

}