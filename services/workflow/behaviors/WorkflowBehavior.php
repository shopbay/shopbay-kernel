<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * WorkflowBehavior class describes all the default behaviors when action is invoked
 * as defined in s_workflow for all object types
 * 
 * WorkflowBehavior::runTransition() is the entry point to trigger workflow transition. 
 *
 * Each Model class has its own WorkflowBehavior file. 
 * 
 * For example, Order class should have OrderWorkflowBehavior, and workflow logics are written inside this file
 * 
 * Use NestedPDO class to support nested transactions in WorkflowBehavior. 
 * This is essential to ensur data integrity; For example transition Order may also trigger Item transitions. 
 * 
 * @author kwlok
 */
class WorkflowBehavior extends CActiveRecordBehavior 
{
    /**
     * Validate transition before action
     * This method is invoked before model Transition is saved
     * Expect child class to implement its logic
     * 
     * @param Transition $transition
     * @return boolean
     */
    protected function validateTransition($transition) 
    {
        return true;
    }  
    /**
     * Validate transition action
     * This method is invoked before method runTransition is invoked
     * 
     * @param Transition $transition
     * @param string $action
     * @return boolean
     */
    public function validateAction($transition,$action) 
    {
        logTrace(__METHOD__.' transition '.$transition->obj_type.' '.$transition->obj_id.' action '.$action);
        
        $validProcess = false;
        foreach (WorkflowManager::getAllProcessesBeforeAction($transition->obj_type,$action) as $process) {
            if ($transition->beginProcess() == $process){
                $validProcess = true;
                logTrace('Matched begin process: '.$process);
            }
            else
                logTrace('Unmatched begin process: '.$process);
        }
        if (!$validProcess){
            logError('Invalid begin process',$transition->getAttributes(),false);
            throw new CException(Sii::t('sii','Invalid begin process'));
        }

        $decisions = WorkflowManager::getPotentialDecisions($transition->obj_type, $transition->process_from, $action);
        if (is_array($decisions)){
            $found = false;
            foreach ($decisions as $decision) {
                if ($transition->decision == $decision)
                    $found = true;
            }
            if (!$found){
                logError('Invalid process decision '.$transition->decision.', expect '.implode(" or ", $decisions),$transition->getAttributes());
                throw new CException(Sii::t('sii','Invalid process decision'));
            }
        }
        else {
            logTrace(__METHOD__.' single decision: '.$decisions);
            //For single decision [A]; currently decision is always having two options [A||B] (refer to s_workflow)
            if (!empty($decisions) && $transition->decision != $decisions){
                logError('Invalid process decision '.$transition->decision.', expect '.$decisions,$transition->getAttributes());
                throw new CException(Sii::t('sii','Invalid process decision'));
            }
        }

        logInfo(__METHOD__.' ok');
            
        return true;
    }      
    /**
     * Validate transition action rollback
     * This method is invoked before method runTransition is invoked
     * 
     * @param Transition $transition
     * @param string $action
     * @return boolean
     */
    public function validateActionRollback($transition,$action) 
    {
        logTrace(__METHOD__.' transition action '.$action,$transition->getAttributes());
        $process = WorkflowManager::getProcessBeforeAction($transition->obj_type,$action);
        if (!is_array($process))
            $process = [$process];//convert into array
        
        if (!in_array($transition->endProcess(),$process)){
            logError(__METHOD__.' Invalid end process, expect ',$process);
            throw new CException(Sii::t('sii','Invalid end process'));
        }
        //as rollback by default is tied to primary decision,
        //no decision validation here
            
        logInfo(__METHOD__.' ok');
        
        return true;
    }      
    /**
     * Execute transition decision
     * 
     * @param string $action
     * @param Transition $transition
     * @return boolean
     */
    public function executeDecision($action,$transition) 
    {
        if ($transition->hasDecision())
            $this->getOwner()->{$action.$transition->decision}($transition);
    }  
    /**
     * Execute transition action
     * 
     * @param Transition $transition 
     * @param string $save True to save transition record
     * @return boolean
     */
    public function executeAction($transition,$save=true) 
    {
        if ($transition->onRollback()){
            logInfo('Start rollback '.$transition->obj_type.' '.$transition->obj_id.' '.$transition->process_from.' -> '.$transition->process_to);
            //for rollback need to retrieve previous process action
            $action = WorkflowManager::getActionBeforeProcess($transition->obj_type,$transition->process_from);
            if ($this->validateActionRollback($transition, $action))
                $this->getOwner()->{'rollback'.$action}($transition);
        }
        else{
            logInfo('Start transition '.$transition->obj_type.' '.$transition->obj_id.' '.$transition->process_from.' -> '.$transition->process_to);
            //$transition action is required to be set at WorkflowManager
            if ($this->validateAction($transition, $transition->action))
                $this->getOwner()->{strtolower($transition->action)}($transition);
        }
        logInfo('End transition '.$transition->obj_type.' '.$transition->obj_id,$transition->getAttributes());
        if ($save==true)
            return $this->_createTransitionRecord($transition);
        else
            return $transition;
    }  

    /**
     * Run transition 
     * This method is invoked before model Transition is saved
     * 
     * @param Transition $transition
     * @return boolean
     */
    public function runTransition($transition, $save=true) 
    {
        if ($this->getOwner()->validateTransition($transition)){
            $transition = $this->executeAction($transition,$save);
            logInfo(__METHOD__.' '.$transition->obj_type.' '.$transition->obj_id.' ok');
            return $transition;
        }
        else {
            logError($e->getMessage(),$transition->getAttributes(),false);
            throw new CException(Sii::t('sii','Failed to validate transition'));
        }
    }  
    /**
     * Default transition behavior without customized business logic
     * 
     * @param Transition $transition
     */
    protected function defaultBehavior($transition,$updateAttributes=['status','update_time']) 
    {
        $this->getOwner()->status = $transition->endProcess();
        $this->getOwner()->update($updateAttributes); 
        logInfo(__METHOD__.' ok');
    }
    /**
     * Default rollback transition behavior without customized business logic
     * 
     * @param Transition $transition
     */
    protected function defaultRollbackBehavior($transition) 
    {
        $this->getOwner()->status = $transition->endProcess();
        $this->getOwner()->update(['status','update_time']); 
        logInfo(__METHOD__.' ok');
    }    
    /**
     * Increase account metric by a quantum of amount 
     * 
     * @param type $account_id
     * @param type $metric
     * @param type $quantum
     */
    protected function increaseAccountMetric($account_id,$metric,$quantum=1) 
    {
        Yii::app()->serviceManager->getAnalyticManager()->setAccountMetric($account_id,$metric,$quantum,AnalyticManager::INCREASE);
        logInfo(__METHOD__.' ok');
    }  
    /**
     * Decrease account metric by a quantum of amount 
     * 
     * @param type $account_id
     * @param type $metric
     * @param type $quantum
     */
    protected function decreaseAccountMetric($account_id,$metric,$quantum=1) 
    {
        Yii::app()->serviceManager->getAnalyticManager()->setAccountMetric($account_id,$metric,$quantum,AnalyticManager::DECREASE);
        logInfo(__METHOD__.' ok');
    }        
    private function _createTransitionRecord($transition) 
    {
        if ($transition->save()) {
            logInfo('transition record saved');
            return $transition;
        }
        else {
            logError('Fail to save transition model',$transition->getErrors(),false);
            throw new CException(Sii::t('sii','Fail to save transition model'));
        }
    }
    /**
     * Get the condition1 label at workflow form
     * @return string
     */
    public function getCondition1Label()
    {
        return Sii::t('sii','Reason');
    }
    /**
     * Show if condition1 is required at workflow form
     * @return string
     */
    public function getCondition1Required()
    {
        return true;//always true
    }
    /**
     * Get the condition1 placeholder text at workflow form
     * @return string
     */
    public function getCondition1Placeholder()
    {
        return null;
    }
    /**
     * Get the condition2 label at workflow form
     * @return string
     */
    public function getCondition2Label()
    {
        return Sii::t('sii','Supporting Information');
    }
    /**
     * Show if condition2 is required at workflow form
     * @return string
     */
    public function getCondition2Required()
    {
        if ($this->getOwner()->getScenario()==Transition::SCENARIO_C1_C2 ||
            $this->getOwner()->getScenario()==Transition::SCENARIO_C1_C2_D)
            return true;
        else
            return false;
    }
    /**
     * Get the condition2 placeholder text at workflow form
     * @return string
     */
    public function getCondition2Placeholder()
    {
        return null;
    }
    /**
     * Get the attachment placeholder text at workflow form
     * @return string
     */    
    public function getAttachmentPlaceholder()
    {
        return Sii::t('sii','e.g. attachment description');
    }  
    /**
     * Get prompt message before decision is executed
     * @return string
     */    
    public function getPromptMessage($decision)
    {
        return null;
    }  
    /**
     * Get workflow object
     * @return type
     */
    public function getWorkflow($action=null)
    {
        $workflow = WorkflowManager::getProcess($this->getOwner()->tableName(), $this->getOwner()->status, $action);
        if ($workflow===null){
            logWarning(__METHOD__.' workflow not found for process: '.$this->getOwner()->status,[], false);
            return null;
        }
        return $workflow;
    }
    /**
     * Return permitted to run workflow
     * @return type
     */
    public function getWorkflowRole()
    {
        $workflow = $this->getWorkflow();
        if ($workflow===null){
            return 'undefined';//so that no role will be matched and give no access
        }
        return $this->getWorkflow()->start_by;
    }
    /**
     * Return workflow action 
     * @return string
     */    
    public function getWorkflowAction($role=null)
    {
        $action = WorkflowManager::getActionAfterProcess($this->getOwner()->tableName(), $this->getOwner()->status, $role);
        if ($action!=null)
            return $action;
        else
            return 'undefined';
    }  
    /**
     * Return workflow decisions 
     * @return array
     */    
    public function getWorkflowDecisions($action=null)
    {
        return WorkflowManager::getDecisionAfterProcess($this->getOwner()->tableName(), $this->getOwner()->status, $action);
    }  
    /**
     * Return workflow next status based on currenct state
     */
    public function getNextStatus($action=null,$decision=null)
    {
        return WorkflowManager::getNextProcess($this->getOwner()->tableName(),$this->getOwner()->status,$action,$decision);
    }
    /**
     * Workflow support method at tasks/WorkflowController
     * @param type $role Need role here to validate $workflow->start_by
     * @param type $user Need user to validate permission
     * @return boolean
     */
    public function actionable($role,$user=null)
    {
        if ($this->isRolePermitted($role)){
            if (isset($user)){
                $access = 'Tasks.'.get_class($this->getOwner()).'.'.$this->getOwner()->getWorkflowAction($role);
                //logTrace(__METHOD__.' Check access '.$access);
                if (Yii::app()->authManager->checkAccess($access,$user)){
                    return true;
                }
                else {
                    logError(__METHOD__.' No access '.$access);
                    return false;
                }
            }
            else {
                logTrace(__METHOD__.' user is null; skip check access');
                return true;
            }
        }
        else {
            return false;
        }
    } 
    /**
     * Workflow support method at tasks/WorkflowController
     * @param type $role Need role here to validate $workflow->start_by
     * @param type $decision
     * @return boolean
     */
    public function decisionable($role,$decision=null)
    {
        if ($this->isRolePermitted($role))
            return true;
        else
            return false;
    }        
    /**
     * Check if role is permitted to run workflow
     * @param type $role
     * @return boolean
     */
    public function isRolePermitted($role)
    {
        if ($role!=$this->getWorkflowRole()){
            //logTrace(__METHOD__.' unauthorized role: '.$role);
            return false;
        }
        else 
            return true;
    }
    /**
     * Return workflow description to show at page 
     */
    public function getWorkflowDescription()
    {
        return null;
    }    
}

