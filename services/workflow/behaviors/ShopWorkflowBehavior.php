<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
/**
 * Description of ShopWorkflowBehavior
 *
 * @author kwlok
 */
class ShopWorkflowBehavior extends WorkflowBehavior 
{
    /**
     * This method describes the behavior of Change action
     * 
     * @param Transition $transition
     */
    protected function change($transition)
    {
        $this->executeDecision(__FUNCTION__,$transition);
        
        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');            
    }
    /**
     * This method describes the behavior of Apply action
     * 
     * @param Transition $transition
     */
    protected function apply($transition)
    {
        $this->executeDecision(__FUNCTION__,$transition);

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
     * This method describes the behavior of Approve action by decision Accept
     */
    protected function approveAccept($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Approve action by decision Reject
     */
    protected function approveReject($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }       
    /**
     * Workflow support method at tasks/WorkflowController
     * @param type $role Need role here to validate $workflow->start_by
     * @param type $user Need user to validate permission
     * @return boolean
     */
    public function actionable($role,$user=null)
    {
        if (parent::actionable($role,$user))
            return $this->getOwner()->pendingApproval();
        else
            return false;
    } 
    /**
     * Workflow support method at tasks/WorkflowController
     * @param type $role Need role here to validate $workflow->start_by
     * @param type $decision
     * @return boolean
     */
    public function decisionable($role,$decision=null)
    {
        if (parent::decisionable($role,$decision)){
            if ($decision==WorkflowManager::DECISION_ACCEPT || $decision==WorkflowManager::DECISION_REJECT)
                return true;
            else
                return false;
        }
        else
            return false;
    }     
}