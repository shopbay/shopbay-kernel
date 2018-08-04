<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
/**
 * TicketWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type that supports transition (draft, submit, close)
 *	 
 * @author kwlok
 */
class TicketWorkflowBehavior extends WorkflowBehavior 
{
    /**
     * This method describes the behavior of Submit action
     * 
     * @param Transition $transition
     */
    protected function submit($transition)
    {        
        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Close action
     * 
     * @param Transition $transition
     */
    protected function close($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Close action by decision Yes
     */
    protected function closeYes($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Close action by decision No
     */
    protected function closeNo($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    } 
    /**
     * This method describes the behavior of Reopen action
     * 
     * @param Transition $transition
     */
    protected function reopen($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
    }     
    /**
     * This method describes the behavior of Reopen action by decision Yes
     */
    protected function reopenYes($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Reopen action by decision No
     */
    protected function reopenNo($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }     
}
