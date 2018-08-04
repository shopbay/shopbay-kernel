<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
/**
 * TransitionWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type that supports transition (online to offline, vice versa)
 *
 *	 
 * @author kwlok
 */
class TransitionWorkflowBehavior extends WorkflowBehavior 
{
    public $subTransitionModelsCallback;
    /**
     * This method describes the behavior of Activate action
     * 
     * @param Transition $transition
     */
    protected function activate($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        $this->runSubTransition(__FUNCTION__,$transition);
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Activate action by decision Yes
     */
    protected function activateYes($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Activate action by decision No
     */
    protected function activateNo($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Deactivate action
     * 
     * @param Transition $transition
     */
    protected function deactivate($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        $this->runSubTransition(__FUNCTION__,$transition);
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Deactivate action by decision Yes
     */
    protected function deactivateYes($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Deactivate action by decision No
     */
    protected function deactivateNo($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method runs sub transition according to the call back 
     */
    protected function runSubTransition($action,$transition)
    {
        if (isset($this->subTransitionModelsCallback)){
            foreach ($this->getOwner()->{$this->subTransitionModelsCallback}(ucfirst($action)) as $subTransitionModel) {
                Yii::app()->serviceManager->model = get_class($subTransitionModel);
                Yii::app()->serviceManager->transition($transition->transition_by,$subTransitionModel,$action);
                logInfo(__METHOD__." $this->subTransitionModelsCallback ok");
            }
        }
    }
    
}
