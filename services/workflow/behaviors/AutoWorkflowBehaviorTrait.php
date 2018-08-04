<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AutoWorkflowBehaviorTrait
 * 
 * Mainly used by Item and ShippingOrder Model only (for auto transitioning)
 *
 * @author kwlok
 */
trait AutoWorkflowBehaviorTrait 
{
    /**
     * Collect order item stats
     */
    protected function getItemStats($shopId,$orderId,$shippingId=null)
    {
        $stats = [];
        //[1] Get all order items 
        $finder = Item::model()->merchant($shopId)->order($orderId);
        if (isset($shippingId))
            $finder = $finder->shipping($shippingId);
        $items = $finder->findAll();
        $stats['total'] = count($items);
        //[2] Check item status
        foreach ($items as $item) {
            if (isset($stats[$item->status]))
                $stats[$item->status]++;
            else
                $stats[$item->status] = 1;//start count
        }
        logTrace(__METHOD__.' data',$stats);
        return $stats;        
    }    
    /**
     * Auto run order workflow to determine if they are fulfilled or partially fulfilled
     * I. FULL FULFILLMENT; 3 SCENARIO
     *  [1] WHEN PARTIALLY FULFILLED
     *  [2] WHEN IT IS DIRECT ONE TIME FULLY FULFILLED
     *  [3] WHEN IT IS UNDER DEFERRED PAYMENT 
     *
     * II. PARTIALLY FULFILLED; RUN ONLY ONCE 
     * 
     * @param type $finder
     * @param array $itemStatues
     * @param type $transition
     * @param type $ownerReference
     * @return type
     */
    protected function autoRunOrderFulfillment($finder,$itemStatues,$transition,$ownerReference)
    {
        if (!($this->getOwner()->$finder instanceof ShippingOrder || $this->getOwner()->$finder instanceof Order))
            throw new CException(__FUNCTION__.' Error: Invalid model');
        
        //[1] Get all order items stats
        $fulfilledItemCnt = 0;
        foreach ($itemStatues as $status => $count) {
            if (in_array($status, array_merge([Process::SHIPPED],Item::model()->getReceivedProcesses())))
                $fulfilledItemCnt += $count;
        }
        if ($itemStatues['total']==$fulfilledItemCnt){
            //FULL FULFILLMENT; 3 SCENARIO
            //[1] WHEN PARTIALLY FULFILLED
            if ($this->getOwner()->$finder->orderPartialFulfilled()){
                Yii::app()->serviceManager->execute($this->getOwner()->$finder, array(
                    ServiceManager::WORKFLOW=>array(
                        'transitionBy'=>$transition->transition_by,
                        'condition'=>array(
                            Transition::MESSAGE=>'System-triggered by '.$ownerReference.' fulfillment',
                        ),
                        'action'=>WorkflowManager::ACTION_FULFILL,
                        'decision'=>WorkflowManager::DECISION_YES,
                        'saveTransition'=>true,
                    )),
                    ServiceManager::NO_VALIDATION
                );
                logTrace(__METHOD__.' full fulfillment ok for '.$finder,$this->getOwner()->$finder->attributes);
            }
            //[2] WHEN IT IS DIRECT ONE TIME FULLY FULFILLED
            if ($this->getOwner()->$finder->orderConfirmed()){
                Yii::app()->serviceManager->execute($this->getOwner()->$finder, array(
                    ServiceManager::WORKFLOW=>array(
                        'transitionBy'=>$transition->transition_by,
                        'condition'=>array(
                            Transition::MESSAGE=>'System-triggered by '.$ownerReference.' fulfillment',
                        ),
                        'action'=>WorkflowManager::ACTION_PROCESS,
                        'decision'=>WorkflowManager::DECISION_FULFILL,
                        'saveTransition'=>true,
                    )),
                    ServiceManager::NO_VALIDATION
                );
                logTrace(__METHOD__.' full fulfillment ok for '.$finder,$this->getOwner()->$finder->attributes);
            }
            //[3] WHEN IT IS UNDER DEFERRED PAYMENT 
            if ($this->getOwner()->$finder->orderDeferred()){
                //Transition PO / SO to next status following the transition decision 
                Yii::app()->serviceManager->execute($this->getOwner()->$finder, array(
                    ServiceManager::WORKFLOW=>array(
                        'transitionBy'=>$transition->transition_by,
                        'condition'=>array(
                            Transition::MESSAGE=>'System-triggered by '.$ownerReference.' fulfillment',
                        ),
                        'action'=> ($this->getOwner()->$finder instanceof ShippingOrder)?WorkflowManager::ACTION_PROCESS:WorkflowManager::ACTION_DELIVER,
                        'decision'=>WorkflowManager::DECISION_FULFILL,
                        'saveTransition'=>true,
                    )),
                    ServiceManager::NO_VALIDATION,
                    ServiceManager::NO_NOTIFICATION//for deferred order, no need to send further notifcation
                );
                logTrace(__METHOD__.' deferred payment full fulfillment ok for '.$finder,$this->getOwner()->$finder->attributes);
                return;
            }
            
        }
        elseif ($fulfilledItemCnt >= 1){
            //PARTIALLY FULFILLED; RUN ONLY ONCE 
            if (!$this->getOwner()->$finder->orderPartialFulfilled()){//only run when it is not in partial fulfilled status
                Yii::app()->serviceManager->execute($this->getOwner()->$finder, array(
                    ServiceManager::WORKFLOW=>array(
                        'transitionBy'=>$transition->transition_by,
                        'condition'=>array(
                            Transition::MESSAGE=>'System-triggered by '.$ownerReference.' partial fulfillment',
                        ),
                        'action'=> ($this->getOwner()->$finder instanceof Order && $this->getOwner()->$finder->orderDeferred()) ? WorkflowManager::ACTION_DELIVER : WorkflowManager::ACTION_PROCESS,
                        'decision'=>WorkflowManager::DECISION_PARTIAL,
                        'saveTransition'=>true,
                    )),
                    ServiceManager::NO_VALIDATION
                );
                logTrace(__METHOD__.' partial fulfillment ok for '.$finder,$this->getOwner()->$finder->attributes);
            }
        }
        logInfo(__METHOD__." $finder ok; fulfilledItemCnt=$fulfilledItemCnt");
    }     
    /**
     * Auto run order workflow to determine if they are to be cancelled 
     * Only do cancel order when all items are in cancelled status
     * 
     * @param type $finder
     * @param array $itemStatues
     * @param type $transition
     * @param type $ownerReference
     * @return type
     */
    protected function autoRunOrderCancellation($finder,$itemStatues,$transition,$ownerReference,$skipItemsWorkflow=false)
    {
        if (!($this->getOwner()->$finder instanceof ShippingOrder || $this->getOwner()->$finder instanceof Order))
            throw new CException(__FUNCTION__.' Error: Invalid model');
        
        if (!$this->getOwner()->$finder->orderCancelled()){
            $cancelledCount = isset($itemStatues[Process::CANCELLED])?$itemStatues[Process::CANCELLED]:0;
            $deferredCancelledCount = isset($itemStatues[Process::DEFERRED_CANCELLED])?$itemStatues[Process::DEFERRED_CANCELLED]:0;
            $outOfStockCount = isset($itemStatues[Process::PICKED_REJECT])?$itemStatues[Process::PICKED_REJECT]:0;
            $badQualityCount = isset($itemStatues[Process::PACKED_REJECT])?$itemStatues[Process::PACKED_REJECT]:0;
            $total = $cancelledCount + $deferredCancelledCount + $outOfStockCount + $badQualityCount;
            if ($itemStatues['total']==$total){
                //Transition PO / SO to cancel status following the transition decision 
                Yii::app()->serviceManager->execute($this->getOwner()->$finder, array(
                    ServiceManager::WORKFLOW=>array(
                        'transitionBy'=>$transition->transition_by,
                        'condition'=>array(
                            Transition::MESSAGE=>'System-triggered to cancel '.$ownerReference,
                            Transition::PAYLOAD=>$finder=='shippingOrder'?$skipItemsWorkflow:false,//a controll param to determine if to trigger item workfkow auto run
                        ),
                        'action'=> ($this->getOwner()->$finder instanceof Order && $this->getOwner()->$finder->orderDeferred()) ? WorkflowManager::ACTION_DELIVER : WorkflowManager::ACTION_PROCESS,
                        'decision'=>WorkflowManager::DECISION_CANCEL,
                        'saveTransition'=>true,
                    )),
                    ServiceManager::NO_VALIDATION
                );
                logInfo(__METHOD__.' ok');
            }          
            else
                logTrace(__METHOD__.' skip');
        }            
        else
            logTrace(__METHOD__.' skip');
    }    
    /**
     * Auto run order workflow to determine if order refund transition is required 
     * Condition: Only when all items in the items are refunded
     * @param type $transition
     */
    protected function autoRunOrderRefund($finder, $itemStatues,$transition, $ownerReference)
    {
        if (!($this->getOwner()->$finder instanceof ShippingOrder || $this->getOwner()->$finder instanceof Order))
            throw new CException(__FUNCTION__.' Error: Invalid model');
        
        if ($this->getOwner()->$finder->orderCancelled()){
            if (isset($itemStatues[Process::REFUND]) && $itemStatues['total']==$itemStatues[Process::REFUND]){//this is used REFUND as previous items already went through transitions from CCL; -> RF;
                Yii::app()->serviceManager->execute($this->getOwner()->$finder, [
                    ServiceManager::WORKFLOW=>[
                        'transitionBy'=>$transition->transition_by,
                        'condition'=>[
                            Transition::MESSAGE=>'System-triggered by refunding '.$ownerReference,
                            Transition::PAYLOAD=>$finder=='shippingOrder'?'skipItemsRefund':'runItemsRefund',//a controll param to determine if to trigger item refund auto run
                        ],
                        'action'=> $transition->action,
                        'decision'=>  WorkflowManager::DECISION_NULL,
                        'saveTransition'=>true,
                    ]],
                    ServiceManager::NO_VALIDATION
                );
                logInfo(__METHOD__.' ok');
            }
            else
                logTrace(__METHOD__.' skip');
        }
        else
            logTrace(__METHOD__.' do not proceed');
    }    
    /**
     * Auto run item workflow by specific action and decision
     * @param type $transition
     * @param type $action
     * @param type $decision
     */
    public function autoRunItemsWorkflow($transition, $action, $decision)
    {
        if (!$this->getOwner() instanceof ShippingOrder)
            throw new CException(__FUNCTION__.' Error: Invalid model');
        
        //[1] Get $status based on $action 
        $status = WorkflowManager::getProcessBeforeAction(Item::model()->tableName(), $action);
        logInfo(__METHOD__.' for $action='.$action.', $decision='.$decision.', $status',$status);
        //[2] Get all $filter items 
        $items = Item::model()->merchant($this->getOwner()->shop_id)
                              ->order($this->getOwner()->order_id)
                              ->shipping($this->getOwner()->shipping_id)
                              ->status($status) 
                              ->findAll();
        //[2] Run workflow for all selected items based on current $status and $decision 
        foreach ($items as $item) {
            Yii::app()->serviceManager->execute($item, array(
                ServiceManager::WORKFLOW=>array(
                        'transitionBy'=>$transition->transition_by,
                        'condition'=>array(
                            Transition::MESSAGE => array(
                                $item->getCondition1Label($decision)=>'System-triggered by auto processing '.$item->shipping_order_no,
                                $item->getCondition2Label($decision)=>'Auto run workflow action '.$action.' by decision '.$decision,
                            )
                        ),
                        'action'=>$action,
                        'decision'=>$decision,
                        'saveTransition'=>true,
                    ),
                ));
        }
        logInfo(__METHOD__.' ok');
    }    
    /**
     * Auto run item refund 
     * @param type $transition
     * @param array $refundData initial refund data
     * @return array $refundData with refunded items data
     */
    public function autoRunItemsRefund($transition,$refundData,$skipWorkflow=false,$itemRefundFilter='cancelled')
    {
        if (!$this->getOwner() instanceof ShippingOrder)
            throw new CException(__FUNCTION__.' Error: Invalid model');
        
        $items = Item::model()->merchant($this->getOwner()->shop_id)
                              ->order($this->getOwner()->order_id)
                              ->shipping($this->getOwner()->shipping_id)
                              ->$itemRefundFilter()
                              ->findAll();
        foreach ($items as $item) {
            //Set all cancelled items to refund 
            if (!$skipWorkflow){
                Yii::app()->serviceManager->execute($item, [
                    ServiceManager::WORKFLOW=>[
                            'transitionBy'=>$transition->transition_by,
                            'condition'=>[
                                Transition::MESSAGE => array(
                                    Sii::t('sii','Refund Amount')=>$item->refundSuggestion,
                                    Sii::t('sii','Supporting Information')=>'System-triggered from refunding shipping order '.$item->shipping_order_no.'. The refund amount is computed assuming full item refund.',
                                ),
                                Transition::PAYLOAD=>'skipShippingOrderRefund',//a controll param to determine if to trigger shipping refund auto run
                            ],
                            'action'=> $transition->action,
                            'decision'=>WorkflowManager::DECISION_NULL,
                            'saveTransition'=>true,
                        ],
                    ]);
            }
            //[4] Only refund item shipping surcharge, order level shipping rate charge not refund
            $refundData['items'][$item->id] = [
                'amount'=>(float)$item->total_price,
                'shipping_surcharge'=>(float)$item->shipping_surcharge,
            ];
            if ($skipWorkflow)
                $refundData['items'][$item->id]['refund_suggestion'] = $item->refundSuggestion;
        }
        
        logInfo(__METHOD__.' ok');
        
        return $refundData;
    }     

}
