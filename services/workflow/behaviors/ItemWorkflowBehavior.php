<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
Yii::import("common.services.workflow.behaviors.AutoWorkflowBehaviorTrait");
/**
 * ItemWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type s_item
 *
 * @author kwlok
 */
class ItemWorkflowBehavior extends WorkflowBehavior 
{
    use AutoWorkflowBehaviorTrait;
    /**
     * Override method
     * 
     * Todo this metric could be not accurate as user role is not passing in; Need to revise if the metric is used anywhere
     * @see WorkflowBehavior::getWorkflowAction()
     * 
     * @see WorkflowBehavior::defaultBehavior()
     * @param type $transition
     */
    protected function defaultBehavior($transition,$updateAttributes=['status','update_time']) 
    {
        $this->increaseAccountMetric(
                $transition->transition_by,
                Metric::PREFIX_UNIT_ITEM.strtolower($this->getWorkflowAction()),
                $this->getOwner()->quantity); 
        parent::defaultBehavior($transition,$updateAttributes);
    }
    /**
     * Override method
     * Todo this metric could be not accurate as user role is not passing in; Need to revise if the metric is used anywhere
     * @see WorkflowBehavior::getWorkflowAction()
     * 
     * @see WorkflowBehavior::defaultRollbackBehavior()
     * @param Transition $transition
     */
    protected function defaultRollbackBehavior($transition,$quantum=1) 
    {
        $currentAction = $this->getWorkflowAction();
        $previousAction = WorkflowManager::getPreviousAction($this->getOwner()->tableName(), $currentAction);
        $this->increaseAccountMetric(
                $transition->transition_by,
                Metric::PREFIX_UNIT_ITEM.strtolower($previousAction),
                $quantum); 
        $this->decreaseAccountMetric(
                $transition->transition_by,
                Metric::PREFIX_UNIT_ITEM.strtolower($currentAction),
                $quantum); 
        parent::defaultRollbackBehavior($transition);
    }
    /**
     * Validate transition before action
     * This method is invoked before model Transition is saved
     * 
     * @param Transition $transition
     * @return boolean
     */
    protected function validateTransition($transition) 
    {
        if (!$transition->onRollback()){
            
            if ($this->getOwner()->newItem()){
                
                if(!$this->getOwner()->validate()){
                    logError('Validation failed',$this->getOwner()->getErrors());
                    throw new CException(Sii::t('sii','Validation Error'));
                }
                
            }

            if ($this->getOwner()->pickable()){
                $inventory = Yii::app()->serviceManager->getInventoryManager()->findInventory($this->getOwner()->product_id,$this->getOwner()->product_sku);
                $inventory->setScenario(Process::ORDERED);
                if (!$inventory->validate()){
                    logError(__METHOD__.' error',$inventory->getErrors());
                    throw new CException(Sii::t('sii','Inventory Validation Error'));
                }                             
            }
        }
        
        logInfo(__METHOD__.' ok');
        
        return true;
    }  
    /**
     * This method describes the behavior of Purchase action
     * 
     * @param Transition $transition
     */
    protected function purchase($transition)
    {
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }
    /**
     * This method describes the behavior of Purchase action by decision Order
     */
    protected function purchaseOrder($transition)
    {
        Yii::app()->serviceManager->getInventoryManager()->holdInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no);
        
        //analytic metric tracking
        $this->touchAnalytics($this->getOwner()->quantity);        
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Purchase action by decision Defer
     */
    protected function purchaseDefer($transition)
    {
        //SAME PROCESS as purchaseOrder
        $this->purchaseOrder($transition);
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Purchase action by decision Hold
     */
    protected function purchaseHold($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Pay action
     * 
     * @param Transition $transition
     */    
    protected function pay($transition) 
    {
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Pay action by decision Pay
     */
    protected function payPay($transition)
    {
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Pay action by decision Cancel
     */
    protected function payCancel($transition)
    {
        //Since payment not made, no need to reverse analytics
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Verify action
     * 
     * @param Transition $transition
     */
    protected function verify($transition)
    {
        if ($transition->onRollback())
            throw new CException(Sii::t('sii','Process cannot be reversed'));

        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
        
    }   
    /**
     * This method describes the behavior of Verify action by decision Accept
     */
    protected function verifyAccept($transition)
    {
        Yii::app()->serviceManager->getInventoryManager()->holdInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no);
        
        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of Verify action by decision Reject
     */
    protected function verifyReject($transition)
    {
        //do nothing
        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of Repay action
     * 
     * @param Transition $transition
     */    
    protected function repay($transition) 
    {
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Repay action by decision repay
     */
    protected function repayRepay($transition)
    {
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Repay action by decision Cancel
     */
    protected function repayCancel($transition)
    {
        //Since payment not made, no need to reverse analytics
        logInfo(__METHOD__.' ok');
    }     
    /**
     * This method describes the behavior of Pick action
     * 
     * @param Transition $transition
     */
    protected function pick($transition)
    {
        if ($transition->onRollback())
            throw new CException(Sii::t('sii','Invalid Action'));

        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        if ($this->getOwner()->threeStepsWorkflow()){
            if ($transition->decision==WorkflowManager::DECISION_CANCEL)
                $this->getOwner()->autoRunOrderCancellation('shippingOrder',$this->getShippingOrderItemStats(),$transition,$this->getOwner()->shipping_order_no);
        }

        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Pick action by decision HasStock
     */
    protected function pickHasStock($transition)
    {
        Yii::app()->serviceManager->getInventoryManager()->soldInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no);
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Pick action by decision NoStock
     */
    protected function pickNoStock($transition)
    {
        Yii::app()->serviceManager->getInventoryManager()->emptyInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no);
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Pick action by decision Cancel
     */
    protected function pickCancel($transition)
    {
        Yii::app()->serviceManager->getInventoryManager()->holdInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no.' CANCEL ITEM '.$this->getOwner()->id,
                true);//rollback hold
        
        //reverse analytics metric tracking
        $this->touchAnalytics(-$this->getOwner()->quantity);//put -ve sign to reverse     
        
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of rollback Pick action
     * Rollback only reverse the transition caused by the primary decision (1)
     * 
     * @param Transition $transition
     */
    protected function rollbackPick($transition)
    {
        if (!$transition->onRollback())
            throw new CException(Sii::t('sii','Invalid Action'));
        
        $this->executeDecision(__FUNCTION__,$transition);
        
        $this->defaultRollbackBehavior($transition,$this->getOwner()->quantity); 

        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Rollback Pick action by decision HasStock
     */
    protected function rollbackPickHasStock($transition)
    {
        //Rollback of event when HasStock is submitted
        $rollback = true;
        Yii::app()->serviceManager->getInventoryManager()->soldInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no,
                $rollback);
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of RollbackPick action by decision NoStock
     */
    protected function rollbackPickNoStock($transition)
    {
        //Rollback of event when NoStock is submitted
        throw new CException(Sii::t('sii','Rollback not allowed'));
    }    
    /**
     * This method describes the behavior of RollbackPick action by decision Cancel
     */
    protected function rollbackPickCancel($transition)
    {
        //Rollback of event when Cancel is submitted
        throw new CException(Sii::t('sii','Rollback not allowed'));
    }    
    /**
     * This method describes the behavior of Pack action
     * 
     * @param Transition $transition
     */
    protected function pack($transition)
    {        
        if ($transition->onRollback())
            throw new CException(Sii::t('sii','Invalid Action'));

        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        if ($this->getOwner()->threeStepsWorkflow()){
            if ($transition->decision==WorkflowManager::DECISION_CANCEL)
                $this->getOwner()->autoRunOrderCancellation('shippingOrder',$this->getShippingOrderItemStats(),$transition,$this->getOwner()->shipping_order_no);
        }

        logInfo(__METHOD__.' ok');
    }  
    /**
     * This method describes the behavior of Pack action by decision Accept
     */
    protected function packAccept($transition)
    {
        //no stock movement, do nothing
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Pack action by decision Reject
     */
    protected function packReject($transition)
    {
        Yii::app()->serviceManager->getInventoryManager()->badInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no);
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Pack action by decision Cancel
     * This is called by item deferred 
     */
    protected function packCancel($transition)
    {
        //same logic as packReject; set bad inventory
        $this->packReject($transition);
        
        //reverse analytics metric tracking
        $this->touchAnalytics(-$this->getOwner()->quantity);//put -ve sign to reverse     
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of rollback Pack action
     * Rollback only reverse the primary decision (1)
     * 
     * @param Transition $transition
     */
    protected function rollbackPack($transition)
    {
        if (!$transition->onRollback())
            throw new CException(Sii::t('sii','Invalid Action'));

        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultRollbackBehavior($transition,$this->getOwner()->quantity); 

        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of Pack action by decision Accept
     */
    protected function rollbackPackAccept($transition)
    {
        //no stock movement since packAccept() has no stock movement, nothing to rollback
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Pack action by decision Reject
     */
    protected function rollbackPackReject($transition)
    {
        //Rollback of event when Reject is submitted
        $rollback = true;
        Yii::app()->serviceManager->getInventoryManager()->badInventory(
                $this->getOwner()->account_id,
                $this->getOwner()->product_id,
                $this->getOwner()->product_sku,
                $this->getOwner()->quantity,
                $this->getOwner()->order_no,
                $rollback);
    }    
    /**
     * This method describes the behavior of Ship action
     * 
     * @param Transition $transition
     */
    protected function ship($transition)
    {
        if ($transition->onRollback())
            throw new CException(Sii::t('sii','Invalid Action'));
        
        if ($transition->hasDecision())
            $this->getOwner()->{__FUNCTION__.$transition->decision}($transition);

        $this->defaultBehavior($transition); 
        
        //below has run here and not inside each own decision as we need to Item status to be updated first.
        //This is required especially for 3 steps workflow at item level to prevent recursive calls 
        if ($this->getOwner()->threeStepsWorkflow()){
            $this->getOwner()->autoRunOrderFulfillment('shippingOrder',$this->getShippingOrderItemStats(),$transition,'item '.$this->getOwner()->id);
            if ($transition->decision==WorkflowManager::DECISION_CANCEL)
                $this->getOwner()->autoRunOrderCancellation('shippingOrder',$this->getShippingOrderItemStats(),$transition,$this->getOwner()->shipping_order_no);
        }
        
        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of Ship action by decision Ship
     */
    protected function shipShip($transition)
    {
        $this->_setTrackingInfo($transition);
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Ship action by decision Collect
     */
    protected function shipCollect($transition)
    {
        //When Collect is submitted, do nothing
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Ship action by decision Cancel
     * This is called by item deferred 
     */
    protected function shipCancel($transition)
    {
        Yii::app()->serviceManager->getInventoryManager()->returnInventory(
            $this->getOwner()->account_id,
            $this->getOwner()->product_id,
            $this->getOwner()->product_sku,
            $this->getOwner()->quantity,
            $this->getOwner()->shipping_order_no.' CANCEL SHIP ITEM '.$this->getOwner()->id);
        
        //reverse analytics metric tracking
        $this->touchAnalytics(-$this->getOwner()->quantity);//put -ve sign to reverse     
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of rollback Ship action
     * Rollback only reverse the primary decision (1)
     * 
     * @param Transition $transition
     */
    protected function rollbackShip($transition)
    {        
        if (!$transition->onRollback())
            throw new CException(Sii::t('sii','Invalid Action'));
        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultRollbackBehavior($transition,$this->getOwner()->quantity); 

        logInfo(__METHOD__.' ok');
    }  
    /**
     * This method describes the behavior of Rollback Ship action by decision Ship
     */
    protected function rollbackShipShip($transition)
    {
        //Rollback of event when Ship is submitted
        throw new CException(Sii::t('sii','Rollback not allowed'));
    }       
    /**
     * This method describes the behavior of Rollback Ship action by decision Collect
     */
    protected function rollbackShipCollect($transition)
    {
        //Rollback of event when Collect is submitted
        throw new CException(Sii::t('sii','Rollback not allowed'));
    }       
    /**
     * This method describes the behavior of Receive action
     * 
     * @param Transition $transition
     */    
    protected function receive($transition) 
    {
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Receive action by decision Receive
     */
    protected function receiveReceive($transition)
    {
        //no specific logic here
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Receive action by decision Return
     */
    protected function receiveReturn($transition)
    {
        //no specific logic here
        logInfo(__METHOD__.' ok');
    } 
    /**
     * This method describes the behavior of Process action
     * 
     * @param Transition $transition
     */    
    protected function process($transition) 
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        //below has run here and not inside each own decision as we need to Item status to be updated first.
        //This is required especially for one step workflow at item level to prevent recursive calls 
        if ($this->getOwner()->oneStepWorkflow()){
            if ($transition->decision==WorkflowManager::DECISION_SHIP)
                $this->getOwner()->autoRunOrderFulfillment('shippingOrder',$this->getShippingOrderItemStats(),$transition,'item '.$this->getOwner()->id);
            if ($transition->decision==WorkflowManager::DECISION_CANCEL)
                $this->getOwner()->autoRunOrderCancellation('shippingOrder',$this->getShippingOrderItemStats(),$transition,$this->getOwner()->shipping_order_no);
        }
        
        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of Process action by decision Ship
     */
    protected function processShip($transition)
    {
        //auto run 3-steps: pick, pack, ship
        $this->pickHasStock($transition);
        $this->packAccept($transition);
        $this->shipShip($transition);
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Process action by decision Cancel
     */
    protected function processCancel($transition)
    {
        //same logic as pickCancel; rollback hold inventory
        $this->pickCancel($transition);
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Review action
     * 
     * @param Transition $transition
     */    
    protected function review($transition) 
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
    }         
    /**
     * This method describes the behavior of ReturnItem action
     * 
     * @param Transition $transition
     */    
    protected function returnItem($transition) 
    {        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of ReturnItem action by decision Accept
     */
    protected function returnItemAccept($transition)
    {
        $inventoryMessage = $this->getOwner()->shipping_order_no.' RETURN ITEM '.$this->getOwner()->id;
        //parsing condition1 (Transition::MESSAGE data structure - stored in condition1)
        $condition1 = json_decode($transition->condition1,true);
        $message = array_values($condition1[Transition::MESSAGE]);//stored at condition 2
        switch ($message[1]) {//$message[1] is condition2 <-- inventory udpate method
            case Inventory::TAKE_OUT:
                logInfo(__METHOD__.' Inventory::TAKE_OUT, do nothing');//inventory already took out in earlier workflow
                break;
            case Inventory::PUT_BACK:
                Yii::app()->serviceManager->getInventoryManager()->returnInventory(
                    $this->getOwner()->account_id,
		            $this->getOwner()->product_id,
                    $this->getOwner()->product_sku,
                    $this->getOwner()->quantity,
                    $inventoryMessage);
                break;
            case Inventory::MARK_AS_BAD:
                Yii::app()->serviceManager->getInventoryManager()->badInventory(
                    $this->getOwner()->account_id,
 	                $this->getOwner()->product_id,
                    $this->getOwner()->product_sku,
                    $this->getOwner()->quantity,
                    $inventoryMessage);
                break;
            default:
                throw new CException(Sii::t('sii','Invalid inventory update method'));
        }
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of ReturnItem action by decision Reject
     */
    protected function returnItemReject($transition)
    {
        //no specific logic here
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Refund action
     * This refunds a particular item under a shipping order 
     * It is triggered under scenario of 'oneStepOrdersItemProcessing' or 'threeStepsOrdersItemProcessing' shop setting
     * @see Workflowable::oneStepWorkflow()
     * @see Workflowable::threeStepsWorkflow()
     * 
     * @param Transition $transition
     */    
    protected function refund($transition) 
    {        
        //try to cancel SO first if possible, so that later SO can be set to refund
        if ($this->getOwner()->outOfStock() || $this->getOwner()->badQuality())//catered from scenario when 3 steps workflow
            $this->getOwner()->autoRunOrderCancellation('shippingOrder',$this->getShippingOrderItemStats(),$transition,$this->getOwner()->shipping_order_no,true);//skip item workflow

        //parsing condition1 (Transition::MESSAGE data structure - stored in condition1)
        //@see ServiceManager::runWorkflow() 
        $condition1 = json_decode($transition->condition1,true);
        $condition2 = json_decode($transition->condition2,true);
        $skipShippingOrderRefund = isset($condition2[Transition::PAYLOAD]) && $condition2[Transition::PAYLOAD]=='skipShippingOrderRefund';
        //When direct item refund; normally is ITEM RETURN case
        $message = array_values($condition1[Transition::MESSAGE]);
        $refundData = [
            'obj_type'=> get_class($this->getOwner()),
            'obj_id'=> $this->getOwner()->id,
            'actual_amount'=> (float)$message[0],//this is used to store refund amount
            'supporting_info'=>$message[1],//this is used to store supporting info
        ];
        
        logTrace(__METHOD__.' refund data json '. json_encode($refundData),$refundData);
        $this->getOwner()->refund = json_encode($refundData);

        $this->defaultBehavior($transition,['status','update_time','refund']); 
        
        //[6] Transition Shipping Order to refund status 
        if (!$skipShippingOrderRefund)
            $this->getOwner()->autoRunOrderRefund('shippingOrder',$this->getShippingOrderItemStats(),$transition, 'item '.$this->getOwner()->id);

        logInfo(__METHOD__.' ok');
    } 
    /**
     * set tracking info for action Ship/Process
     */
    private function _setTrackingInfo($transition)
    {
        if (!$this->getOwner()->skipWorkflow()){
            //When OK is submitted
            $message = $transition->getMessage(true);
            $this->getOwner()->tracking_no = $message->{$this->getCondition1Label()};
            $this->getOwner()->tracking_url = $message->{$this->getCondition2Label()};
        }
        logInfo(__METHOD__.' ok');
    }      
    /**
     * @override
     * @see WorkflowBehavhior::getCondition1Label()
     */    
    public function getCondition1Label($decision=null)
    {
        if (($this->getOwner()->itemDeferred() && $this->getOwner()->oneStepWorkflow())||
            ($this->getOwner()->itemDeferredPacked() && $this->getOwner()->threeStepsWorkflow()))
            return Sii::t('sii','Payment Amount');
        elseif ($this->getOwner()->pickable())
            return Sii::t('sii','Note');
        elseif ($this->getOwner()->packable())
            return Sii::t('sii','Inspection / Quality Check Result');
        elseif ($this->getOwner()->shippable()){
            if ($this->getOwner()->oneStepWorkflow())
                return Sii::t('sii','Shipping Information / Reason to cancel item');
            else
                return Sii::t('sii','Shipping Information');
        }
        elseif ($this->getOwner()->receivable($this->getOwner()->account_id))
            return Sii::t('sii','Item Condition');
        elseif ($this->getOwner()->refundable())
            return Sii::t('sii','Refund Amount');
        else
            return parent::getCondition1Label();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition1Placeholder()
     */    
    public function getCondition1Placeholder($decision=null)
    {
        if (($this->getOwner()->itemDeferred() && $this->getOwner()->oneStepWorkflow())||
            ($this->getOwner()->itemDeferredPacked() && $this->getOwner()->threeStepsWorkflow()))
            return Sii::t('sii','Please enter the payment amount made by customer on delivery. If cancel item, please enter item total.');
        elseif ($this->getOwner()->pickable())
            return Sii::t('sii','Make a note.');
        elseif ($this->getOwner()->packable())
            return Sii::t('sii','Record down the stock quality.');
        elseif ($this->getOwner()->shippable())
            return Sii::t('sii','e.g. Carrier and Tracking No, or order / payment no etc');
        else if ($this->getOwner()->receivable($this->getOwner()->account_id))
            return $this->getOwner()->getReturnReasons();
        else if ($this->getOwner()->returnable())
            return Sii::t('sii','Make a note for either accepting or rejecting return request.');
        else if ($this->getOwner()->refundable())
            return Sii::t('sii','Please enter the refund amount.');
        else
            return parent::getCondition1Placeholder();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Label()
     */    
    public function getCondition2Label($decision=null)
    {
        if (($this->getOwner()->itemDeferred() && $this->getOwner()->oneStepWorkflow())||
            ($this->getOwner()->itemDeferredPacked() && $this->getOwner()->threeStepsWorkflow()))
            return Sii::t('sii','Supporting Information');
        elseif ($this->getOwner()->shippable())
            return Sii::t('sii','Tracking Url');
        else if ($this->getOwner()->receivable($this->getOwner()->account_id))
            return Sii::t('sii','Delivery Date / Others Information');
        else if ($this->getOwner()->returnable())
            return Sii::t('sii','Inventory Handling');
        else if ($this->getOwner()->refundable())
            return Sii::t('sii','Refund Information');
        else
            return parent::getCondition2Label();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Placeholder()
     */    
    public function getCondition2Placeholder($decision=null)
    {
        if (($this->getOwner()->itemDeferred() && $this->getOwner()->oneStepWorkflow())||
            ($this->getOwner()->itemDeferredPacked() && $this->getOwner()->threeStepsWorkflow()))
            return Sii::t('sii','e.g. the delivery order information and date time etc. You may also upload any COD payment evidence for future reference. If cancel order, please enter reason for cancellation.');
        elseif ($this->getOwner()->shippable())
            return Sii::t('sii','Please enter the parcel tracking url');
        else if ($this->getOwner()->receivable($this->getOwner()->account_id))
            return Sii::t('sii','For item return, you may want to give more supporting information e.g. the delivery date, detailed item description etc.');
        else if ($this->getOwner()->returnable())
            return Inventory::getHandlingMethods();
        else if ($this->getOwner()->refundable())
            return Sii::t('sii','e.g. refund payment method, recipient, transaction reference number, refund date time etc');
            //return PaymentMethod::getRefundPaymentMethods();
        else
            return parent::getCondition2Placeholder();
    }    
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Required()
     */
    public function getCondition2Required($decision=null)
    {
         if (($this->getOwner()->itemDeferred() && $this->getOwner()->oneStepWorkflow())||
            ($this->getOwner()->itemDeferredPacked() && $this->getOwner()->threeStepsWorkflow()))
            return true;
        elseif (isset($decision) && $decision==WorkflowManager::DECISION_RETURN)
            return true;
        elseif ($this->getOwner()->returnable())
            return true;
        else if ($this->getOwner()->refundable())
            return true;
        else
            return parent::getCondition2Required();
    }    
    /**
     * @override
     * @see WorkflowBehavhior::getPromptMessage()
     */    
    public function getPromptMessage($decision)
    {
        if ($this->getOwner()->packable() && $decision==WorkflowManager::DECISION_REJECT)
            return Sii::t('sii','Are you sure you want to reject packing this item?');
        elseif ($decision==WorkflowManager::DECISION_REJECT)
            return Sii::t('sii','Are you sure you want to reject the return of this item?');
        else if ($decision==WorkflowManager::DECISION_NOSTOCK)
            return Sii::t('sii','Setting product SKU "{sku}" to "Out of Stock" will empty its inventory.',['{sku}'=>$this->getOwner()->product_sku]).' '.
                   Sii::t('sii','Please note that this step is irreversible.')."\n".
                   Sii::t('sii','Are you sure you want to proceed?');
        else
            return parent::getPromptMessage($decision);
    }      
    /**
     * Workflow support method at tasks/WorkflowController
     * @param type $role Need role here to validate $workflow->start_by
     * @param type $user Need user to validate permission
     * @return boolean
     */
    public function actionable($role,$user=null)
    {
        if (!isset($user))
            $user = Account::GUEST;//to support guest checkout
        
        if ($user==Account::GUEST||parent::actionable($role,$user)){
            if ($role==Role::CUSTOMER){
                return $this->getOwner()->receivable($user) || $this->getOwner()->reviewable();
            }
            else {
                if ($role==Role::MERCHANT && !$this->getOwner()->skipWorkflow()){
                    if ($this->getOwner()->oneStepWorkflow()){//1 step workflow
                        if ($this->getOwner()->processable() || $this->getOwner()->returnable() || $this->getOwner()->refundable())
                            return true;
                        else
                            return false;
                    }
                    else { //3 steps workflow
                        return $this->getOwner()->hasShippingOrder && !($this->getOwner()->fulfillable() || $this->getOwner()->verifiable());//when item is reach fulfillable stage
                    }
                }
                else
                    return $this->allowReturn() && ( $this->getOwner()->returnable() || $this->getOwner()->refundable());
            }
        }
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
            
            if ($role==Role::CUSTOMER) {
                if ($decision==WorkflowManager::DECISION_RETURN)
                    return $this->getOwner()->allowReturn();
                else
                    return true;
            }
            else {
                if ($role==Role::MERCHANT && !$this->getOwner()->skipWorkflow()){
                    if ($this->getOwner()->oneStepWorkflow() && $decision==WorkflowManager::DECISION_CANCEL)
                        return true;
                    if ($this->getOwner()->threeStepsWorkflow() && ($this->getOwner()->itemDeferred()||$this->getOwner()->itemDeferredPicked()||$this->getOwner()->itemDeferredPacked()) && $decision==WorkflowManager::DECISION_CANCEL)
                        return true;
                    if ($this->getOwner()->threeStepsWorkflow() && $decision==WorkflowManager::DECISION_CANCEL)
                        return false;
                    if ($decision==WorkflowManager::DECISION_CANCEL || $decision==WorkflowManager::DECISION_PAY)
                        return false;
                    else
                        return true;
                }  
                else
                    return $this->allowReturn() && ($this->getOwner()->returnable() || $this->getOwner()->refundable());
            }
        }
        else
            return false;
    }    
    /**
     * Get workflow object
     * It will return corresponding workflow object depends on item processing mode
     * @override
     * @return type
     */
    public function getWorkflow($action=null)
    {
        if ($this->getOwner()->oneStepWorkflow() && $this->getOwner()->processable()){
            $action = WorkflowManager::ACTION_PROCESS;
        }
        
        return parent::getWorkflow($action);
    }   
    /**
     * Return workflow action 
     * It will return corresponding workflow object depends on item processing mode
     * @return string
     */    
    public function getWorkflowAction($role=null)
    {
        if (isset($role) && $role==Role::MERCHANT && $this->getOwner()->oneStepWorkflow() && $this->getOwner()->processable())
            return WorkflowManager::ACTION_PROCESS;//ACTION_PROCESS only applies to Merchant role
        else
            return parent::getWorkflowAction($role);
    }     
    /**
     * Return workflow description to show at page 
     */
    public function getWorkflowDescription()
    {
        if ($this->getOwner()->itemShipped())
           return Sii::t('sii','Please confirm you have received this item. If you would like to return item for good reason, you may also raise the request here.');
        elseif ($this->getOwner()->pickable())
            return Sii::t('sii','Pick a stock from inventory. If inventory has no more stock, click "Out of Stock" by not fulfilling this item.');
        elseif ($this->getOwner()->packable())
            return Sii::t('sii','Examine the quality of the stock. When condition is good and ready to ship, pack it else reject the stock.');
        elseif ($this->getOwner()->returnable())
           return Sii::t('sii','Please accept or reject return request. If you accept return, you need to specify how do you want to handle inventory for the returned item.');
        else 
           return parent::getWorkflowDescription();
    }   
    /**
     * Check if show allows item return
     * @return boolean
     */
    protected function allowReturn()
    {
        return $this->getOwner()->shop->isReturnAllowed;
    }    
    /**
     * Update analytic data
     * @param int $quantum Quantum unit to update
     */
    protected function touchAnalytics($quantum)
    {
        $this->trackItemSale($quantum);
        $this->trackItemPurchase($quantum);
    }    
    /**
     * Track Item Sale (For analytics used by merchant account)
     */
    protected function trackItemSale($quantity) 
    {
        Yii::app()->serviceManager->getAnalyticManager()->updateItemSale(
                $this->getOwner()->shop->account_id,
                $this->getOwner()->shop_id,
                $quantity,
                $this->getOwner()->currency);
        logInfo(__METHOD__.' ok');
    }    
    /**
     * Track Item Purchase (For analytics used by customer account)
     */
    protected function trackItemPurchase($quantity) 
    {
        Yii::app()->serviceManager->getAnalyticManager()->updateItemPurchase(
                $this->getOwner()->account_id,
                $this->getOwner()->shop_id,
                $quantity,
                $this->getOwner()->currency);
        logInfo(__METHOD__.' ok');
    }    
    /**
     * Collect shipping order item stats
     * @see AutoWorkflowBehaviorTrait::getItemStats()
     */
    protected function getShippingOrderItemStats()
    {
        return $this->getItemStats($this->getOwner()->shop_id,$this->getOwner()->order_id,$this->getOwner()->shipping_id);
    }
    
}