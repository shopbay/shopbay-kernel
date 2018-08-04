<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.orders.components.OrderData");
Yii::import("common.modules.orders.components.OrderNumberGenerator");
Yii::import("common.modules.orders.models.ShippingOrder");
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
Yii::import("common.services.AnalyticManager");
/**
 * OrderWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type s_order
 *
 * @author kwlok
 */
class OrderWorkflowBehavior extends WorkflowBehavior 
{
    /**
     * Override method
     * @param type $transition
     */
    protected function defaultBehavior($transition,$updateAttributes=['status','update_time']) 
    {
        $this->increaseAccountMetric($transition->transition_by,Metric::PREFIX_UNIT_PO.strtolower($this->getWorkflowAction())); 
        parent::defaultBehavior($transition,$updateAttributes);
    }
    /**
     * This method describes the behavior of Purchase action
     * 
     * Transition->condition2 stores both Items DTO and Shipping Address DTO
     * 
     * @param Transition $transition
     */
    protected function purchase($transition)
    {
        if (!$this->getOwner()->newOrder())
            throw new CException(Sii::t('sii','Invalid initial order status'));

        //Bulk Transistion Items
        Yii::app()->serviceManager->execute(Item::model(), array(
            ServiceManager::WORKFLOW_BATCH=>array(
                'models'=>$this->_createItems($this->_getDTO(OrderManager::DTO_ITEMS,$transition)),
                'transitionBy'=>$transition->transition_by,
                'condition'=>array(
                    Transition::MESSAGE=>'System-triggered by Order '.$this->getOwner()->order_no,
                ),
                'action'=>$transition->action,
                'decision'=>$transition->decision,
                'saveTransition'=>true,
            )),
            ServiceManager::NO_VALIDATION
        );
        
        $this->_createOrderShippingAddress($this->_getDTO(OrderManager::DTO_SHIPPING_ADDRESS,$transition));

        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 

        logInfo(__METHOD__.' ok');
            
    }
    /**
     * This method describes the behavior of Purchase action by decision Order
     */
    protected function purchaseOrder($transition)
    {
        //payment record is already created by OrderManager
        
        //Create ShippingOrder record
        $this->_createShippingOrder(Process::ORDERED);
        
        //analytic metric tracking
        $this->touchAnalytics($transition,AnalyticManager::INCREASE);
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Purchase action by decision Defer
     */
    protected function purchaseDefer($transition)
    {
        //Create ShippingOrder record
        $this->_createShippingOrder(Process::DEFERRED);
        
        //analytic metric tracking
        $this->touchAnalytics($transition,AnalyticManager::INCREASE);
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Purchase action by decision Hold
     */
    protected function purchaseHold($transition)
    {
        //no specific business logic here
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Pay action
     * 
     * @param Transition $transition
     */
    protected function pay($transition) 
    {        
        if ($transition->hasDecision())
            $this->getOwner()->{__FUNCTION__.$transition->decision}($transition);
            
        //Bulk Transistion order Items
        Yii::app()->serviceManager->execute(Item::model(), array(
            ServiceManager::WORKFLOW_BATCH=>array(
                'models'=>$this->getOwner()->items,
                'transitionBy'=>$transition->transition_by,
                'condition'=>array(
                    Transition::MESSAGE=>'System-triggered by Order '.$this->getOwner()->order_no,
                ),
                'action'=>$transition->action,
                'decision'=>$transition->decision,
                'saveTransition'=>true,
            )),
            ServiceManager::NO_VALIDATION
        );
        
        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }  
    /**
     * This method describes the behavior of Pay action by decision Pay
     */
    protected function payPay($transition)
    {
        //Create payment record
        Yii::app()->serviceManager->execute(Payment::model(), [
            ServiceManager::PAYMENT=> [
                'service'=>'pay',
                'paymentData'=>[
                    'id' => $this->getOwner()->getPaymentMethodId(),
                    'shop_id' => $this->getOwner()->shop_id,
                    'payer'  => $this->getOwner()->account_id,//recipient
                    'type'   => Payment::SALE,
                    'status' => Process::UNPAID,
                    'method' => $this->getOwner()->getPaymentMethodMode(),
                    'amount' => $this->getOwner()->grand_total,
                    'reference_no'  => $this->getOwner()->order_no,
                    'currency'  => $this->getOwner()->getCurrency(),
                    'trace_no' => $transition->condition1,//here is encoded Transition::MESSAGE
                ],
            ]],
            ServiceManager::NO_VALIDATION
        );    
        
        //analytic metric tracking
        $this->touchAnalytics($transition,AnalyticManager::INCREASE);
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Pay action by decision Cancel
     */
    protected function payCancel($transition)
    {
        //Since payment not made, no need to create payment record and shipping order
        logInfo(__METHOD__.' ok');
    }  
    /**
     * This method describes the behavior of Verify action
     * 
     * @param Transition $transition
     */
    protected function verify($transition)
    {
        if (!$this->getOwner()->orderPendingVerified())
            throw new CException(Sii::t('sii','Order not paid'));
        
        if ($transition->hasDecision())
            $this->getOwner()->{__FUNCTION__.$transition->decision}($transition);

        $this->defaultBehavior($transition); 
            
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Verify action by decision Accept
     */
    protected function verifyAccept($transition)
    {
        //Get all items by order id
        $items = Item::model()->merchant($this->getOwner()->shop_id)
                              ->order($this->getOwner()->id)
                              ->findAll();
        //Bulk Transistion order Items to next status 
        Yii::app()->serviceManager->execute(Item::model(), array(
            ServiceManager::WORKFLOW_BATCH=>array(
                'models'=>$items,
                'transitionBy'=>$transition->transition_by,
                'condition'=>array(
                    Transition::MESSAGE=>'System-triggered by accepting payment for '.$this->getOwner()->order_no,
                ),
                'action'=>$transition->action,
                'decision'=>$transition->decision,
                'saveTransition'=>true,
            )),
            ServiceManager::NO_VALIDATION
        );
        
        //Create ShippingOrder record
        $this->_createShippingOrder(Process::ORDERED);
        
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Verify action by decision Reject
     */
    protected function verifyReject($transition)
    {
        //Get all items by order id
        $items = Item::model()->merchant($this->getOwner()->shop_id)
                              ->order($this->getOwner()->id)
                              ->findAll();
        //Bulk Transistion order Items to next status 
        Yii::app()->serviceManager->execute(Item::model(), array(
            ServiceManager::WORKFLOW_BATCH=>array(
                'models'=>$items,
                'transitionBy'=>$transition->transition_by,
                'condition'=>array(
                    Transition::MESSAGE=>'System-triggered by rejecting payment for '.$this->getOwner()->order_no,
                ),
                'action'=>$transition->action,
                'decision'=>$transition->decision,
                'saveTransition'=>true,
            )),
            ServiceManager::NO_VALIDATION
        );
        
        //Need to void previous payment record as during workflow "payPay" a payment record is created
        Yii::app()->serviceManager->execute(Payment::model(), [
            ServiceManager::PAYMENT=>[
                'service'=>'void',
                'paymentData'=>[
                    'id' => $this->getOwner()->getPaymentMethodId(),//dummy
                    'shop_id' => $this->getOwner()->shop_id,//dummy
                    'payer'  => $this->getOwner()->account_id,//recipient
                    'type'   => Payment::VOID,//dummy
                    'status' => Process::PAID,//dummy
                    'method' => $this->getOwner()->getPaymentMethodMode(),//dummy
                    'amount' => $this->getOwner()->grand_total,//dummy
                    'reference_no'  => $this->getOwner()->order_no,
                    'currency'  => $this->getOwner()->getCurrency(),//dummy
                ],
            ]],
            ServiceManager::NO_VALIDATION
        );    
        
        logInfo(__METHOD__.' ok');
    }  
    /**
     * This method describes the behavior of Repay action
     * 
     * @param Transition $transition
     */
    protected function repay($transition) 
    {        
        if ($transition->hasDecision())
            $this->getOwner()->{__FUNCTION__.$transition->decision}($transition);
            
        //Bulk Transistion order Items
        Yii::app()->serviceManager->execute(Item::model(), array(
            ServiceManager::WORKFLOW_BATCH=>array(
                'models'=>$this->getOwner()->items,
                'transitionBy'=>$transition->transition_by,
                'condition'=>array(
                    Transition::MESSAGE=>'System-triggered by Order '.$this->getOwner()->order_no,
                ),
                'action'=>$transition->action,
                'decision'=>$transition->decision,
                'saveTransition'=>true,
            )),
            ServiceManager::NO_VALIDATION
        );
        
        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }  
    /**
     * This method describes the behavior of Repay action by decision Repay
     */
    protected function repayRepay($transition)
    {
        //Create payment record
        //todo but the payment amount should be the delta? since repay will not be the full amount?
        Yii::app()->serviceManager->execute(Payment::model(), array(
            ServiceManager::PAYMENT=>array(
                'service'=>'pay',
                'paymentData'=>array(
                    'id' => $this->getOwner()->getPaymentMethodId(),
                    'shop_id' => $this->getOwner()->shop_id,
                    'payer'  => $this->getOwner()->account_id,//recipient
                    'type'   => Payment::SALE,
                    'status' => Process::UNPAID,
                    'method' => $this->getOwner()->getPaymentMethodMode(),
                    'amount' => $this->getOwner()->grand_total,//todo this amount should be the amount enter at transition form?
                    'reference_no'  => $this->getOwner()->order_no,
                    'currency'  => $this->getOwner()->getCurrency(),
                    'trace_no' => $transition->condition1,//here is encoded Transition::MESSAGE
                ),
            )),
            ServiceManager::NO_VALIDATION
        );    
        
        //analytic metric tracking
        $this->touchAnalytics($transition,AnalyticManager::INCREASE);
        
        logInfo(__METHOD__.' ok');
    }   
    /**
     * This method describes the behavior of Repay action by decision Cancel
     */
    protected function repayCancel($transition)
    {
        //Since payment not made, no need to create payment record and shipping order
        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of Process action
     * 
     * @param Transition $transition
     */
    protected function process($transition)
    {
        if (!$this->getOwner()->orderConfirmed())
            throw new CException(Sii::t('sii','Order cannot be processed'));
        
        if ($transition->hasDecision())
            $this->getOwner()->{__FUNCTION__.$transition->decision}($transition);

        $this->defaultBehavior($transition); 
            
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Process action by decision Fulfill
     */
    protected function processFulfill($transition)
    {
        //no specific business logic here
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
     * This method describes the behavior of Cancel action by decision Process 
     * Only when all items are cancelled then order status is set to cancelled
     * @see ShippingOrderWorkflowBehavior::processCancel()
     */
    protected function processCancel($transition)
    {
        if (!$this->getOwner()->cancellable())
            throw new CException(Sii::t('sii','Order cannot be cancelled'));
        
        //analytic metric tracking
        $this->touchAnalytics($transition, AnalyticManager::DECREASE);
        
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
            throw new CException(Sii::t('sii','Order cannot be fulfilled'));
        
        $this->executeDecision(__FUNCTION__,$transition);

        $this->defaultBehavior($transition); 
        
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Fulfill action by decision Yes
     */
    protected function fulfillYes($transition)
    {
        //no specific business logic here
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
     * This method describes the behavior of Deliver action
     * 
     * @param Transition $transition
     */
    protected function deliver($transition)
    {
        if (!$this->getOwner()->orderDeferred())
            throw new CException(Sii::t('sii','Order is in incorrect state to be delivered'));
        
        if ($transition->hasDecision())
            $this->getOwner()->{__FUNCTION__.$transition->decision}($transition);

        $this->defaultBehavior($transition); 
            
        logInfo(__METHOD__.' ok');
    }    
    /**
     * This method describes the behavior of Deliver action by decision Fulfill
     */
    protected function deliverFulfill($transition)
    {
        //no specific business logic here
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Deliver action by decision Partial
     */
    protected function deliverPartial($transition)
    {
        //no specific business logic here
        logInfo(__METHOD__.' ok');
    }       
    /**
     * This method describes the behavior of Deliver action by decision Cancel
     */
    protected function deliverCancel($transition)
    {
        if (!$this->getOwner()->cancellable())
            throw new CException(Sii::t('sii','Order cannot be cancelled'));

        //analytic metric tracking
        $this->touchAnalytics($transition, AnalyticManager::DECREASE);
        
        logInfo(__METHOD__.' ok');
    }      
    /**
     * This method describes the behavior of Refund action
     * Order only in REFUND status when all items / shipping order are refunded.
     * 
     * @param Transition $transition
     */
    protected function refund($transition)
    {
        if (!$this->getOwner()->orderCancelled())
            throw new CException(Sii::t('sii','Order is not cancelled'));
        
        $refundData = [
            'obj_type'=> get_class($this->getOwner()),
            'obj_id'=> $this->getOwner()->id,
            'actual_amount'=> 0,//initial value
            'items'=> [],//initial value
            'shipping_orders'=> [],//initial value
        ];
        //[2] Get all shipping orders 
        $shippingOrders = ShippingOrder::model()->merchant($this->getOwner()->shop_id)
                                       ->orderNo($this->getOwner()->order_no)
                                       ->refunded()
                                       ->findAll();
        foreach ($shippingOrders as $shippingOrder) {
            //[3] Merging shipping order refund info
            $refundData['actual_amount'] += $shippingOrder->actualRefundAmount;
            $refundData['items'] = array_merge($refundData['items'],$shippingOrder->refundItems);
            $refundData['shipping_orders'][] = $shippingOrder->shipping_no;
        }
        
        logTrace(__METHOD__.' refund data json '. json_encode($refundData),$refundData);
        $this->getOwner()->refund = json_encode($refundData);

        $this->defaultBehavior($transition,['status','update_time','refund']); 
            
        logInfo(__METHOD__.' ok');
    }     
    /**
     * Create (or clone) order for merchant to process
     * Customer and Merchant order are stored separately in two tables
     */
    private function _createShippingOrder($status=null)
    {        
        if (!($this->getOwner()->newOrder()||$this->getOwner()->orderPendingVerified()))
            throw new CException(Sii::t('sii','Order must be new or paid to proceed'));

        foreach ($this->getOwner()->getShippings() as $shipping) {
            $shippingOrder = new ShippingOrder();
            $shippingOrder->shop_id = $this->getOwner()->shop_id;
            $shippingOrder->account_id = $this->getOwner()->shop->account_id;
            $shippingOrder->shipping_id = $shipping;
            $shippingOrder->shipping_no = (new OrderNumberGenerator($shippingOrder))->generate();
            $shippingOrder->order_id = $this->getOwner()->id;
            $shippingOrder->order_no = $this->getOwner()->order_no;
            $shippingOrder->payment_method = $this->getOwner()->payment_method;
            $shippingOrder->status = $status==null?WorkflowManager::beginProcess($shippingOrder->tableName()):$status;
            //retrieve all purchased items of this order 
            $items = Item::model()->order($this->getOwner()->id)->locateShop($this->getOwner()->shop_id)->shipping($shipping)->findAll();
            foreach($items as $item){
                $shippingOrder->item_count += $item->quantity;
                $shippingOrder->item_total += $item->total_price;
            }
            $shippingOrder->item_shipping = json_encode($this->getOwner()->getShippingData($shipping));
            //calculate grand total after discount after tax
            $orderData = Yii::app()->serviceManager->getOrderManager()->calculatePriceAfterDiscountAfterTax($this->getOwner(),$shippingOrder->item_total,$this->getOwner()->getShippingRate($shipping));
            $shippingOrder->discount = $orderData->discountData;
            $shippingOrder->tax = $orderData->taxData;
            $shippingOrder->grand_total = $orderData->grandTotal;
            
            Yii::app()->serviceManager->execute($shippingOrder, array(
                'createShippingOrder'=>ServiceManager::EMPTY_PARAMS,
                ServiceManager::NOTIFICATION=>ServiceManager::EMPTY_PARAMS,
            ));            
        }  
        logInfo(__METHOD__.' ok');
    }
    /**
     * Create OrderAddress record
     * 
     * @param type $dto Data transfer object
     */
    private function _createOrderShippingAddress($dto)
    {
        logTrace(__METHOD__.' data transfer object: ',$dto);
        //link shipping address to this order and save shipping address
        if ($dto!=null){
            $shippingAddress = new OrderAddress('shipping');
            $shippingAddress->order_id = $this->getOwner()->id;
            //$dto = json_decode($dto);
            foreach($dto as $key => $value)
                $shippingAddress->{$key} = $value;
            $shippingAddress->insert();
            logInfo(__METHOD__.' ok');
        }
        else
            logTrace(__METHOD__.' skipped');
            
    }
    /**
     * Create Item records
     * 
     * @param array $dtoArray array of Data transfer object
     * @throws CException
     */
    private function _createItems($dtoArray)
    {
        logTrace(__METHOD__.' data transfer object: ',$dtoArray);
        $models = new CList();
        foreach($dtoArray as $dto){
            $item = new Item();
            $item->account_id = $this->getOwner()->account_id;
            $item->order_id = $this->getOwner()->id;
            $item->order_no = $this->getOwner()->order_no;
            $item->status = WorkflowManager::beginProcess($item->tableName());
            foreach($dto as $key => $value)
                $item->{$key} = $value;
            $item->insert();
            $models->add($item);    
            logTrace(__METHOD__,$item->getAttributes());
        }

        logInfo(__METHOD__.' ok');
   
        return $models->toArray();
    }    
    
    private function _getDTO($type,$transition)
    {
        $payload = $transition->getPayload();
        return $payload->{$type};
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition1Label()
     */    
    public function getCondition1Label($decision=null)
    {
        if ($this->getOwner()->orderOnHold()){
            return Sii::t('sii','Payment Amount');
        }
        return parent::getCondition1Label();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition1Placeholder()
     */    
    public function getCondition1Placeholder($decision=null)
    {
        if ($this->getOwner()->orderOnHold())
            if (!isset($decision) || (isset($decision)&&$decision==WorkflowManager::DECISION_PAY))
                return Sii::t('sii','Please enter the amount you are paying. This normally should be the order total price.');
        if ($this->getOwner()->orderPendingVerified())
            return Sii::t('sii','e.g. the verified payment amount and information; You may also reject order if payment amount is not tally with order total, and record the amount discrepancies.');
        return parent::getCondition1Placeholder();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Label()
     */    
    public function getCondition2Label($decision=null)
    {
        if ($this->getOwner()->orderOnHold()){
            if (!isset($decision))
                return Sii::t('sii','Payment Information / Reason to cancel order');
            if (isset($decision)&&$decision==WorkflowManager::DECISION_PAY)
                return Sii::t('sii','Payment Information');
        }
        return parent::getCondition2Label();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Placeholder()
     */    
    public function getCondition2Placeholder($decision=null)
    {
        if ($this->getOwner()->orderOnHold())
            if (!isset($decision) || (isset($decision)&&$decision==WorkflowManager::DECISION_PAY))
                return Sii::t('sii','e.g. transaction reference number provided by bank, bank name, bank account initial etc, or reason to cancel order');
        return parent::getCondition2Placeholder();
    }
    /**
     * @override
     * @see WorkflowBehavhior::getCondition2Required()
     */
    public function getCondition2Required($decision=null)
    {
        if ($this->getOwner()->orderOnHold())
            return true;
        else
            return parent::getCondition2Required();
    }      
    /**
     * @override
     * @see WorkflowBehavhior::getAttachmentPlaceholder()
     */    
    public function getAttachmentPlaceholder($decision=null)
    {
        if ($this->getOwner()->orderOnHold())
            if (!isset($decision) || (isset($decision)&&$decision==WorkflowManager::DECISION_PAY))
                return Sii::t('sii','e.g. ATM receipt, or screenshots');
        return parent::getAttachmentPlaceholder();
    }    
    /**
     * @override
     * @see WorkflowBehavhior::getPromptMessage()
     */    
    public function getPromptMessage($decision)
    {
        if ($decision==WorkflowManager::DECISION_CANCEL)
            return Sii::t('sii','Are you sure you want to cancel this order?');
        else
            return parent::getPromptMessage($decision);
    }      
    /**
     * Workflow support method at tasks/WorkflowController
     * @param type $role Need role here to validate $workflow->start_by
     * @param type $user Need user to validate permission; for Guest checkout support as well
     * @return boolean
     */
    public function actionable($role,$user=null)
    {
        if ($user==Account::GUEST||parent::actionable($role,$user)){
            if ($role==Role::MERCHANT)
                return $this->getOwner()->orderPendingVerified();
            else
                return $this->getOwner()->orderOnHold();//mainly for Role::CUSTOMER
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
            
            if ($role==Role::CUSTOMER){
                if ($decision==WorkflowManager::DECISION_PAY || $decision==WorkflowManager::DECISION_REPAY || $decision==WorkflowManager::DECISION_CANCEL)
                    return true;
            }
            else {
                if ($role==Role::MERCHANT){
                    if ($decision==WorkflowManager::DECISION_ACCEPT || $decision==WorkflowManager::DECISION_REJECT)
                        return true;
                }
            }            
        }
        
        return false;
    }    
    /**
     * Return workflow description to show at page 
     */
    public function getWorkflowDescription()
    {
        if ($this->getOwner()->orderOnHold())
           return Sii::t('sii','Fill in the requested information to proceed payment and click "{action}". If you want to cancel order, click "Cancel".',['{action}'=>Process::getActionText($this->getOwner()->getWorkflowAction())]);
        elseif ($this->getOwner()->orderPendingVerified())
           return Sii::t('sii','Customer had paid this order. Click "Accept" if you find the payment amount is right and have received the payment.');
        else 
           return parent::getWorkflowDescription();
    }        
    /**
     * Update analytic data
     * @param type $transition
     * @param int $quantum Quantum unit to update
     */
    protected function touchAnalytics($transition,$quantum)
    {
        if ($quantum>0){
            $order_unit = $quantum;
            $item_unit = $this->getOwner()->item_count;
            $grand_total = $this->getOwner()->grand_total;
            $this->increaseAccountMetric($transition->transition_by,Metric::TOTAL_ORDER_SPENT, $grand_total);
        }
        else {
            $order_unit = $quantum;
            $item_unit = -$this->getOwner()->item_count;//put to negative
            $grand_total = -$this->getOwner()->grand_total;//put to negative
            $this->decreaseAccountMetric($transition->transition_by,Metric::TOTAL_ORDER_SPENT, $grand_total);
        }
        $this->trackOrderSale($order_unit,$grand_total);
        $this->trackOrderPurchase($order_unit,$grand_total);
        $this->trackCustomer($order_unit, $item_unit, $grand_total);
    }
    /**
     * Track Order Sale (For analytics used by merchant account)
     */
    protected function trackOrderSale($order_unit,$grand_total) 
    {
        Yii::app()->serviceManager->getAnalyticManager()->updateOrderSale(
                $this->getOwner()->shop->account_id,
                $this->getOwner()->shop_id,
                $order_unit,
                $grand_total,
                $this->getOwner()->currency);
        logInfo(__METHOD__.' ok');
    }
    /**
     * Track Order Purchase (For analytics used by customer account)
     */
    protected function trackOrderPurchase($order_unit,$grand_total) 
    {
        Yii::app()->serviceManager->getAnalyticManager()->updateOrderPurchase(
                $this->getOwner()->account_id,
                $this->getOwner()->shop_id,
                $order_unit,
                $grand_total,
                $this->getOwner()->currency);
        logInfo(__METHOD__.' ok');
    }
    /**
     * Track customer purchase
     */
    protected function trackCustomer($order_unit,$item_unit,$grand_total) 
    {
        Yii::app()->serviceManager->getAnalyticManager()->trackCustomer(
                $this->getOwner()->shop->account_id,
                $this->getOwner()->shop_id,
                $this->getOwner()->account_id,
                $order_unit,
                $item_unit,
                $grand_total,
                $this->getOwner()->currency);
        logInfo(__METHOD__.' ok');
    }    
}
