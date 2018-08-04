<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
/**
 * TutorialWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type that supports transition (draft, submit, publish)
 *	 
 * @author kwlok
 */
class TutorialWorkflowBehavior extends WorkflowBehavior 
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
     * This method describes the behavior of Publish action
     * 
     * @param Transition $transition
     */
    protected function publish($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Publish action by decision Yes
     */
    protected function publishYes($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Publish action by decision No
     */
    protected function publishNo($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
}
