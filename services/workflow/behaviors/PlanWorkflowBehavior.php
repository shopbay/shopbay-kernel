<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
/**
 * PlanWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type that supports transition (draft, submit, approve)
 *	 
 * @author kwlok
 */
class PlanWorkflowBehavior extends WorkflowBehavior 
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
     * This method describes the behavior of Approve action
     * 
     * @param Transition $transition
     */
    protected function approve($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Approve action by decision Yes
     */
    protected function approveYes($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Approve action by decision No
     */
    protected function approveNo($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
}
