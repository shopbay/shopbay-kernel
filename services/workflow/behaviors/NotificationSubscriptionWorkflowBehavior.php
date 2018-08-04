<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.TransitionWorkflowBehavior");
/**
 * NotificationSubscriptionWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type that supports transition (online to offline, vice versa)
 *
 *	 
 * @author kwlok
 */
class NotificationSubscriptionWorkflowBehavior extends TransitionWorkflowBehavior 
{
    /**
     * This method describes the behavior of Subscribe action
     * 
     * @param Transition $transition
     */
    protected function subscribe($transition)
    {        
        $this->activate($transition);

        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Unsubscribe action
     * 
     * @param Transition $transition
     */
    protected function unsubscribe($transition)
    {        
        $this->deactivate($transition);
        
        logInfo(__METHOD__.' ok');
    }   
    
}
