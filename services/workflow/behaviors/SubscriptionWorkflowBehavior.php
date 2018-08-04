<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
Yii::import("common.modules.plans.models.Subscription");
Yii::import("common.modules.plans.models.SubscriptionAssignment");
/**
 * Description of SubscriptionWorkflowBehavior
 *
 * @author kwlok
 */
class SubscriptionWorkflowBehavior extends WorkflowBehavior 
{
    /**
     * This method describes the behavior of Cancel action
     * 
     * @param Transition $transition
     */
    protected function cancel($transition)
    {        
        //keep end_date as it is; safe since status already changed 
        //$this->getOwner()->end_date = Helper::getMySqlDateFormat(time());//set to today
        $this->getOwner()->status = $transition->endProcess();
        //$this->getOwner()->update(['status','end_date','update_time']); 
        $this->getOwner()->update(['status','update_time']); 
        
        logInfo(__METHOD__.' ok');
    }  
    /**
     * This method describes the behavior of Activate action
     * 
     * @param Transition $transition
     */
    protected function activate($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Activate action by decision Yes
     */
    protected function activateYes($transition)
    {
        $this->getOwner()->start_date = Helper::getMySqlDateFormat(time());
        $this->getOwner()->end_date = $this->getOwner()->parseEndDate();
        $this->getOwner()->status = $transition->endProcess();
        $this->getOwner()->update(['status','start_date','end_date','update_time']);         
        
        //Assign plan as role to users
        $this->assignPermission($this->getOwner()->account_id, $this->getOwner()->planName);
        
        //Send notfication for new subscription
        Yii::app()->serviceManager->execute($this->getOwner(), array(
            ServiceManager::NOTIFICATION=>ServiceManager::EMPTY_PARAMS,
        ),ServiceManager::NO_VALIDATION);        
        
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

        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Deactivate action by decision Yes
     */
    protected function deactivateYes($transition)
    {
        $this->getOwner()->status = $transition->endProcess();
        $this->getOwner()->update(['status','update_time']); 
                
        //Send notfication for cancelled subscription
        Yii::app()->serviceManager->execute($this->getOwner(), array(
            ServiceManager::NOTIFICATION=>ServiceManager::EMPTY_PARAMS,
        ),ServiceManager::NO_VALIDATION);  
        
        //revoke permission
        $this->revokePermission($this->getOwner()->account_id,$this->getOwner()->plan->name,'DEACTIVATE');

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
     * This method describes the behavior of Reactivate action
     * 
     * @param Transition $transition
     */
    protected function reactivate($transition)
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Reactivate action by decision Yes
     */
    protected function reactivateYes($transition)
    {
        $this->getOwner()->status = $transition->endProcess();
        $this->getOwner()->update(['status','update_time']);         
        
        //Send notfication for new subscription
        Yii::app()->serviceManager->execute($this->getOwner(), array(
            ServiceManager::NOTIFICATION=>ServiceManager::EMPTY_PARAMS,
        ),ServiceManager::NO_VALIDATION);        
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Reactivate action by decision Hold
     */
    protected function reactivateHold($transition)
    {
        $this->getOwner()->status = $transition->endProcess();
        $this->getOwner()->update(['status','update_time']);         
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Reactivate action by decision No
     */
    protected function reactivateNo($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }        
    /**
     * This method describes the behavior of Pastdue action
     * 
     * @param Transition $transition
     */
    protected function pastdue($transition)
    {       
        $this->defaultBehavior($transition);
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Expire action
     * 
     * @param Transition $transition
     */
    protected function expire($transition)
    {       
        $this->defaultBehavior($transition);
        
        //revoke permission
        $this->revokePermission($this->getOwner()->account_id,$this->getOwner()->plan->name,'EXPIRE');

        logInfo(__METHOD__.' ok');
    }         
    /**
     * This method describes the behavior of Suspend action
     * 
     * @param Transition $transition
     */
    protected function suspend($transition)
    {       
        $this->defaultBehavior($transition);

        //revoke permission
        $this->revokePermission($this->getOwner()->account_id,$this->getOwner()->plan->name,'SUSPEND');
        
        logInfo(__METHOD__.' ok');
    }
    /**
     * This method describes the behavior of ForceSuspend action
     *
     * @param Transition $transition
     */
    protected function forceSuspend($transition)
    {
        $this->defaultBehavior($transition);

        //revoke permission
        $this->revokePermission($this->getOwner()->account_id,$this->getOwner()->plan->name,'FORCE-SUSPEND');

        logInfo(__METHOD__.' ok');
    }
    /**
     * Assign permission into RBAC framework
     * TODO: To call app\components\rbac\SubscriptionRbacManager::assignRole() rather than primitively invoke sql query
     * @param type $userId
     * @param type $planName
     */
    public function assignPermission($userId,$planName)
    {
        if (!SubscriptionAssignment::model()->locateRbac($userId,$planName)->exists()){
            $query = "INSERT INTO `s_rbac_assignment` (`item_name`,`user_id`,`created_at`) VALUES (:itemname,:uid,:create_time)";
            $command = Yii::app()->db->createCommand($query);
            $command->execute([':uid'=>$userId,':itemname'=>$planName,':create_time'=>time()]);
            logInfo(__METHOD__.' ok',$planName);
        }
        else {
            logInfo(__METHOD__." Skip! plan $planName and user $userId already exists");
        }
    }
    /**
     * Revoke permission into RBAC framework
     * TODO: To call app\components\rbac\SubscriptionRbacManager::revokeRole() rather than primitively invoke sql query
     * @param type $userId
     * @param type $planName
     */
    public function revokePermission($userId,$planName,$trigger)
    {
        //update to other item name as a backup and keep its history also
        $historyPlanName = Helper::rightTrim($this->getOwner()->subscription_no.'.'.$planName.'.'.$trigger.'.'.time(),100);
        $query1 = "INSERT INTO `s_rbac_assignment_history` (`item_name`,`user_id`,`created_at`) VALUES (:itemname,:uid,:create_time)";
        $command = Yii::app()->db->createCommand($query1);
        $command->execute([':uid'=>$userId,':itemname'=>$historyPlanName,':create_time'=>time()]);
        logInfo(__METHOD__.' history created ok. ',$historyPlanName);
        
        //only delete when there is no more active/notExpired plan id that user is subscribing to; if not, skip deletion (as other active subscribed plan under same plan id need the rbac assignment)
        if (Subscription::model()->myPlan($userId,$this->getOwner()->plan_id)->active()->notExpired()->count()==0){//normally at this stage the status should be PCCL;, hence for active/notExpired subscription the count should be zero
            $query2 = "DELETE FROM `s_rbac_assignment` WHERE `user_id`=:uid AND `item_name`=:itemname";
            $command2 = Yii::app()->db->createCommand($query2);
            $command2->execute(array(':uid'=>$userId,':itemname'=>$planName));
            logInfo(__METHOD__.' deleted ok. ',$planName);
        }
        else {
            logInfo(__METHOD__.' Skip! User subscribes to more than one plan id '.$this->getOwner()->plan_id,$planName);
        }
    }
    
}
