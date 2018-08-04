<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
/**
 * Description of QuestionWorkflowBehavior
 *
 * @author kwlok
 */
class QuestionWorkflowBehavior extends WorkflowBehavior 
{
    /**
     * This method describes the behavior of Ask action
     * 
     * @param Transition $transition
     */
    protected function ask($transition)
    {
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
            
    }
    /**
     * This method describes the behavior of Answer action
     * 
     * @param Transition $transition
     */
    protected function answer($transition)
    {
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
            
    }
}
