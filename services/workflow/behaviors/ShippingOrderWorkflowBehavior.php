<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
Yii::import("common.services.workflow.behaviors.AutoWorkflowBehaviorTrait");
/**
 * ShippingOrderWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type s_shipping_order
 *
 * @author kwlok
 */
class ShippingOrderWorkflowBehavior extends WorkflowBehavior 
{
    use AutoWorkflowBehaviorTrait;
    /**
     * Override method
     * @param type $transition
     */
    protected function defaultBehavior($transition,$updateAttributes=['status','update_time']) 
    {
        $this->increaseAccountMetric($transition->transition_by,Metric::PREFIX_UNIT_SO.strtolower($this->getWorkflowAction())); 
        parent::defaultBehavior($transition,$updateAttributes);
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
        logInfo(__METHOD__.' ok');
        return true;
    }    
    /**
     * This method describes the behavior of Process action
     * Two decisions: (1) Fullfil (2) Cancel
     *  
     * @param Transition $transition
     */
    protected function process($transition)
    {
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
                    
        //below has run here and not inside each own decision as we need to ShippingOrder status to be updated first.
        //This is required especially for one step or 3 steps workflow at item level to prevent recursive calls
        if (in_array($transition->decision,[WorkflowManager::DECISION_FULFILL,WorkflowManager::DECISION_PARTIAL]))
            $this->getOwner()->autoRunOrderFulfillment('order',$this->getOrderItemStats(),$transition,$this->getOwner()->shipping_no);

        if ($transition->decision==WorkflowManager::DECISION_CANCEL){
            $condition2 = json_decode($transition->condition2,true);
            $skipItemsWorkflow = isset($condition2[Transition::PAYLOAD]) && $condition2[Transition::PAYLOAD]==true;
            if (!$skipItemsWorkflow)
                $this->getOwner()->autoRunItemsWorkflow($transition,WorkflowManager::ACTION_PROCESS,WorkflowManager::DECISION_CANCEL);
            
            $this->getOwner()->autoRunOrderCancellation('order',$this->getOrderItemStats(),$transition,$this->getOwner()->order_no);
        }        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Process action by decision Fulfill
     */
    protected function processFulfill($transition)
    {
        if ($this->getOwner()->skipWorkflow()){
            if (!$this->getOwner()->processable())
                throw new CException(Sii::t('sii','Order is not processable'));
            //Auto run 1-step item workflow
            $this->getOwner()->autoRunItemsWorkflow($transition,WorkflowManager::ACTION_PROCESS,WorkflowManager::DECISION_SHIP);
            //Auto run 3-steps item workflow
            //$this->getOwner()->autoRunItemsWorkflow($transition,WorkflowManager::ACTION_PICK,WorkflowManager::DECISION_HASSTOCK);
            //$this->getOwner()->autoRunItemsWorkflow($transition,WorkflowManager::ACTION_PACK,WorkflowManager::DECISION_ACCEPT);
            //$this->getOwner()->autoRunItemsWorkflow($transition,WorkflowManager::ACTION_SHIP,WorkflowManager::DECISION_SHIP);
            //create payment record for deferred payment
            if ($this->getOwner()->orderDeferred()){
                logTrace(__METHOD__.' transition data',$transition->attributes);
                //parsing condition1 (Transition::MESSAGE data structure - stored in condition1)
                //@see ServiceManager::runWorkflow() 
                $condition1 = json_decode($transition->condition1,true);
                $message = array_values($condition1[Transition::MESSAGE]);
                Yii::app()->serviceManager->execute(Payment::model(), [
                    ServiceManager::PAYMENT=> [
                        'service'=>'pay',
                        'paymentData'=>[
                            'id' => $this->getOwner()->order->getPaymentMethodId(),
                            'shop_id' => $this->getOwner()->shop_id,
                            'payer'  => $this->getOwner()->order->account_id,//recipient
                            'type'   => Payment::SALE,
                            'status' => Process::UNPAID,
                            'method' => $this->getOwner()->order->getPaymentMethodMode(),
                            'amount' => (float)$message[0],//this is used to store payment amount
                            'reference_no'  => $this->getOwner()->shipping_no,
                            'currency'  => $this->getOwner()->order->getCurrency(),
                            'trace_no' => $transition->condition1,//here is encoded Transition::MESSAGE
                        ],
                    ]],
                    ServiceManager::NO_VALIDATION
                );   
            }
        }
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Process action by decision Partial
     */
    protected function processPartial($transition)
    {
        //no specific business logic here
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Process action by decision Cancel
     */
    protected function processCancel($transition)
    {
        //see logic at the end of process()
        logInfo(__METHOD__.' ok');
    }     
    /**
     * This method describes the behavior of Fulfill action
     * Two decisions: (1) Yes (2) No
     *  
     * @param Transition $transition
     */
    protected function fulfill($transition)
    {
        if (!$this->getOwner()->orderPartialFulfilled())
            throw new CException(Sii::t('sii','Shipping Order cannot be fulfilled'));
        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Fulfill action by decision Yes
     */
    protected function fulfillYes($transition)
    {
        $this->getOwner()->autoRunOrderFulfillment('order',$this->getOrderItemStats(),$transition,$this->getOwner()->shipping_no);
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Fulfill action by decision No
     */
    protected function fulfillNo($transition)
    {
        //no specific business logic here
        logInfo(__METHOD__.' ok');
    }          
    /**
     * This method describes the behavior of Refund action
     * 
     * @param Transition $transition
     */
    protected function refund($transition)
    {   
        if (!$this->getOwner()->orderCancelled())
            throw new CException(Sii::t('sii','Order is not cancelled'));

        //init refund data
        $refundData = [
            'obj_type'=> get_class($this->getOwner()),
            'obj_id'=> $this->getOwner()->id,
            'include_shipping_rate'=> Helper::parseBool(Config::getBusinessSetting('refund_shipping_fee')),
            'actual_amount'=> 0,//initial value
            'items'=> [],//initial value
        ];
        //[2] Auto item refund
        //parsing condition1 (Transition::MESSAGE data structure - stored in condition1)
        //@see ServiceManager::runWorkflow() 
        logTrace(__METHOD__.' transition data',$transition->attributes);
        $condition1 = json_decode($transition->condition1,true);
        $condition2 = json_decode($transition->condition2,true);
        $skipItemsRefund = isset($condition2[Transition::PAYLOAD]) && $condition2[Transition::PAYLOAD]=='skipItemsRefund';
        if ($skipItemsRefund){
            //this is triggered from item level refund
            $refundData = $this->getOwner()->autoRunItemsRefund($transition,$refundData, true, 'refunded');//skip auto item refund (Avoid endless loop
            //manual compute 'actual_amount'
            foreach ($refundData['items'] as $itemId => $itemData) 
                $refundData['actual_amount'] += $itemData['refund_suggestion'];
            $refundData['supporting_info'] = 'This refund is triggered from item refund. Please note that the displayed refund amount is derived from item refund total and may not be actual.';
        }
        else {
            if (isset($condition1[Transition::MESSAGE])){
                $message = array_values($condition1[Transition::MESSAGE]);
                //initial refund data
                $refundData['actual_amount'] = (float)$message[0];//this is used to store refund amount
                $refundData['supporting_info'] = $message[1];//this is used to store supporting info
                $refundData = $this->getOwner()->autoRunItemsRefund($transition,$refundData);
            }
        }
        //[3] Set refund data (note that grand_total already includes discount and tax), shipping rate not refundable 
        logTrace(__METHOD__.' refund data json '. json_encode($refundData),$refundData);
        $this->getOwner()->refund = json_encode($refundData);

        //[4] update status and save refund data; Need to run this before step [8]
        $this->defaultBehavior($transition,['status','update_time','refund']); 

        //[5] Transition Purchase Order to refund status
        $this->getOwner()->autoRunOrderRefund('order',$this->getOrderItemStats(),$transition, $this->getOwner()->shipping_no);

        logInfo(__METHOD__.' ok');
        
    }    
    /**
     * @override
     * @see WorkflowBehavhior::getCondition1Label()
     */    
    public function getCondition1Label($decision=null)
    {
        if ($this->getOwner()->refundable())
            return Sii::t('sii','Refund Amount');
        elseif ($this->getOwner()->orderDeferred())
            return Sii::t('sii','Payment Amount');
        else
            return parent::getCondition1Label();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition1Placeholder()
     */    
    public function getCondition1Placeholder($decision=null)
    {
        if ($this->getOwner()->refundable())
            return Sii::t('sii','Please enter the refund amount.');
        elseif ($this->getOwner()->orderDeferred())
            return Sii::t('sii','Please enter the payment amount made by customer on delivery. If cancel order, please enter order total.');
        else
            return parent::getCondition1Placeholder();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Label()
     */    
    public function getCondition2Label($decision=null)
    {
        if ($this->getOwner()->refundable())
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
        if ($this->getOwner()->refundable())
            return Sii::t('sii','e.g. refund payment method, recipient, transaction reference number, refund date time etc');
            //return PaymentMethod::getRefundPaymentMethods();
        elseif ($this->getOwner()->orderDeferred())
            return Sii::t('sii','e.g. the delivery order information and date time etc. You may also upload any COD payment evidence for future reference. If cancel order, please enter reason for cancellation.');
        else
            return parent::getCondition2Placeholder();
    }    
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Required()
     */
    public function getCondition2Required($decision=null)
    {
        if ($this->getOwner()->shippable())
            return true;
        elseif ($this->getOwner()->orderDeferred())
            return true;
        elseif (isset($decision) && $decision==WorkflowManager::DECISION_RETURN)
            return true;
        elseif ($this->getOwner()->refundable())
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
        if ($decision==WorkflowManager::DECISION_CANCEL)
            return Sii::t('sii','Are you sure you want to cancel this shipping order?');
        elseif ($this->getOwner()->orderDeferred() && $decision==WorkflowManager::DECISION_FULFILL)
            return Sii::t('sii','A payment record will be created for this deferred payment based on the amount entered. Please verify the payment amount is correct to what you have received. Click OK to proceed.');
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
        if (parent::actionable($role,$user)){
            
            if ($this->getOwner()->skipWorkflow())
                return true;
            else if ($this->getOwner()->oneStepWorkflow()){
                return $this->getOwner()->cancellable() || $this->getOwner()->refundable();//support cancel when all items are not yet processed
            }
            else if ($this->getOwner()->fulfillable() 
                  || $this->getOwner()->cancellable() 
                  || $this->getOwner()->orderCancelled()
                 )
                return true;
            else
                return false;
        }
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
            
            if ($this->getOwner()->skipWorkflow()){
                if ($decision==WorkflowManager::DECISION_PARTIAL)//this is handled via auto workflow
                    return false;
                else
                    return true;
            }
            else if ($this->getOwner()->oneStepWorkflow()){
                return $this->getOwner()->cancellable() && $decision==WorkflowManager::DECISION_CANCEL;//disable full / partial fulfill <-- this is auto handled at AutoWorkflowBehaviorTrait
            }
            else {
                if ($decision==WorkflowManager::DECISION_FULFILL)
                    return $this->getOwner()->fulfillable();
                elseif ($decision==WorkflowManager::DECISION_CANCEL)
                    return $this->getOwner()->cancellable();
                else
                    return false;
            }
        }
        else
            return false;
    }     
    /**
     * Return workflow description to show at page 
     */
    public function getWorkflowDescription()
    {
        if ($this->getOwner()->verifiable())
           return Sii::t('sii','Customer had paid this order. Click "Accept" if you find the payment amount is right and have received the payment.');
        elseif ($this->getOwner()->orderCancelled())
           return Sii::t('sii','This shipping order has been cancelled. Please proceed refund to customer.');
        else if ($this->getOwner()->oneStepWorkflow() || $this->getOwner()->threeStepsWorkflow())
           return Sii::t('sii','Check the ordered items and the payment you have received. When all ok and you can start ship each purchased item. If you do not want to process this order for valid reasons, Click "Cancel". All items under this order will be cancelled as well.');
        elseif ($this->getOwner()->processable())
           return Sii::t('sii','Check the ordered items and the payment you have received. When all ok and you have shipped the purchased items, click "Fulfill"; If you do not want to process this order for valid reasons, Click "Cancel".');
        else 
           return parent::getWorkflowDescription();
    }       
    /**
     * Collect order item stats
     * @see AutoWorkflowBehaviorTrait::getItemStats()
     */
    protected function getOrderItemStats()
    {
        return $this->getItemStats($this->getOwner()->shop_id,$this->getOwner()->order_id);
    }    
}

