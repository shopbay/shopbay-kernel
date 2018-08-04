<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
/**
 * CartWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type s_cart
 *	 
 * @author kwlok
 */
class CartWorkflowBehavior extends WorkflowBehavior 
{
    /**
     * This method describes the behavior of Checkout action
     * 
     * @param Transition $transition
     */
    protected function checkout($transition)
    {
        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }        
    /**
     * This method describes the behavior of FillShippingAddress action
     * 
     * @param Transition $transition
     */
    protected function fillShippingAddress($transition)
    {
        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }        
    /**
     * This method describes the behavior of SelectPaymentMethod action
     * 
     * @param Transition $transition
     */
    protected function selectPaymentMethod($transition)
    {
        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }        
    /**
     * This method describes the behavior of Confirm action
     * 
     * @param Transition $transition
     */
    protected function confirm($transition)
    {
        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }        
    /**
     * This method describes the behavior of Complete action
     * 
     * @param Transition $transition
     */
    protected function complete($transition)
    {
        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }        
    
}

