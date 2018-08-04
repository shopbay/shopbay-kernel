<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
/**
 * Description of Workflowable
 * - Define common use query scopes
 *
 * @author kwlok
 */
abstract class Workflowable extends Transitionable 
{
    /*
     * FOR CUSTOMER ORDERS
     */
    protected function _unpaidCondition() 
    {
        return 'status =\''.Process::UNPAID.'\'';
    }
    protected function _deferredCondition() 
    {
        return 'status =\''.Process::DEFERRED.'\'';
    }
    protected function _paidCondition() 
    {
        return 'status =\''.Process::PAID.'\'';
    }
    protected function _orderedCondition() 
    {
        return 'status =\''.Process::ORDERED.'\'';
    }
    protected function _cancelledCondition() 
    {
        return 'status=\''.Process::CANCELLED.'\'';
    }
    protected function _deferredCancelledCondition() 
    {
        return 'status=\''.Process::DEFERRED_CANCELLED.'\'';
    }
    protected function _acceptedCondition() 
    {
        return 'status IN (\''.Process::PICKED_ACCEPT.'\',\''.Process::PACKED_ACCEPT.'\',\''.Process::DEFERRED_PICKED_ACCEPT.'\',\''.Process::DEFERRED_PACKED_ACCEPT.'\')';
    }
    protected function _rejectedCondition() 
    {
        return 'status IN (\''.Process::ORDER_REJECTED.'\',\''.Process::PICKED_REJECT.'\',\''.Process::PACKED_REJECT.'\',\''.Process::RETURNED_REJECT.'\')';
    }
    protected function _pickedCondition() 
    {
        return 'status IN (\''.Process::PICKED_ACCEPT.'\',\''.Process::DEFERRED_PICKED_ACCEPT.'\')';
    }
    protected function _packedCondition() 
    {
        return 'status IN (\''.Process::PACKED_ACCEPT.'\',\''.Process::DEFERRED_PACKED_ACCEPT.'\')';
    }
    protected function _shippedCondition() 
    {
        return 'status=\''.Process::SHIPPED.'\'';
    }
    protected function _collectedCondition() 
    {
        return 'status=\''.Process::COLLECTED.'\'';
    }
    protected function _refundedCondition() 
    {
        return 'status=\''.Process::REFUND.'\'';
    }
    protected function _receivedCondition() 
    {
        return 'status=\''.Process::RECEIVED.'\'';
    }
    protected function _pendingReturnCondition() 
    {
        return 'status=\''.Process::RETURNED_PENDING.'\'';
    }
    protected function _returnedCondition() 
    {
        return 'status=\''.Process::RETURNED_ACCEPT.'\'';
    }
    protected function _reviewedCondition() 
    {
        return 'status=\''.Process::REVIEWED.'\'';
    }
    protected function _processedCondition()
    {
        return 'status IN (\''.Process::ORDER_FULFILLED.'\',\''.Process::CANCELLED.'\')';
    }
    protected function _fulfilledCondition() 
    {
        return 'status =\''.Process::ORDER_FULFILLED.'\'';
    }    
    protected function _partialFulfilledCondition() 
    {
        return 'status =\''.Process::ORDER_PARTIAL_FULFILLED.'\'';
    }    
    protected function mergeWithCriteria($criteria) 
    {
        //logTrace('mergeWithCriteria',$criteria);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * All possible statuses for Purhcase Orders and Items
     * @return type
     */
    public function all() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_deferredCondition(),'OR');
        $criteria->addCondition($this->_paidCondition(),'OR');
        $criteria->addCondition($this->_orderedCondition(),'OR');
        $criteria->addCondition($this->_cancelledCondition(),'OR');
        $criteria->addCondition($this->_deferredCancelledCondition(),'OR');
        $criteria->addCondition($this->_refundedCondition(),'OR');
        if ($this instanceof Order){
            $criteria->addCondition($this->_unpaidCondition(),'OR');
            $criteria->addCondition($this->_rejectedCondition(),'OR');
            $criteria->addCondition($this->_fulfilledCondition(),'OR');
            $criteria->addCondition($this->_partialFulfilledCondition(),'OR');
        }
        if ($this instanceof ShippingOrder){
            $criteria->addCondition($this->_fulfilledCondition(),'OR');
            $criteria->addCondition($this->_partialFulfilledCondition(),'OR');
        }
        if ($this instanceof Item){
            $criteria->addCondition($this->_unpaidCondition(),'OR');
            $criteria->addCondition($this->_acceptedCondition(),'OR');
            $criteria->addCondition($this->_rejectedCondition(),'OR');
            $criteria->addCondition($this->_shippedCondition(),'OR');
            $criteria->addCondition($this->_collectedCondition(),'OR');
            $criteria->addCondition($this->_receivedCondition(),'OR');
            $criteria->addCondition($this->_reviewedCondition(),'OR');
            $criteria->addCondition($this->_pendingReturnCondition(),'OR');
            $criteria->addCondition($this->_returnedCondition(),'OR');
        }
        return $this->mergeWithCriteria($criteria);        
    }
    /**
     * For item, pending means those pending processed by merchants
     * @return type
     */
    public function pending() 
    {
        $criteria=new CDbCriteria(); 
        if ($this instanceof Item){
            $criteria->addCondition($this->_acceptedCondition(),'OR');
            $criteria->addCondition($this->_pendingReturnCondition(),'OR');
        }
        return $this->mergeWithCriteria($criteria);
    }
    /**
     * Pending (grouping) definition for items. should be same as method pending() above
     * @return array
     */
    public function getPendingProcesses()
    {
        if ($this instanceof Item){
            return array(
                Process::PICKED_ACCEPT,
                Process::PACKED_ACCEPT,
                Process::DEFERRED_PICKED_ACCEPT,
                Process::DEFERRED_PACKED_ACCEPT,
                Process::RETURNED_PENDING);
        }
    }
    /**
     * Rejected (grouping) definition for items. should be same as method rejected() above
     * @return array
     */
    public function getRejectedProcesses()
    {
        if ($this instanceof Item){
            return array(
                Process::ORDER_REJECTED,
                Process::PICKED_REJECT,
                Process::PACKED_REJECT,
                Process::RETURNED_REJECT);
        }
    }    
    /**
     * Received (grouping) definition for items. should be same as method received() 
     * @return array
     */
    public function getReceivedProcesses()
    {
        if ($this instanceof Item){
            return array(
                Process::RECEIVED,
                Process::REVIEWED,
                Process::COLLECTED,
            );
        }
        return null;
    }    
    /**
     * Define the auto refund process triggered by order refund
     * Only Process::RETURN_ACCEPT is handled by manual refund
     * 
     * @return array
     */
    public function getAutoRefundProcesses($excludes=[])
    {
        if ($this instanceof Item){
            $processes = [
                Process::CANCELLED,
                Process::PICKED_REJECT,
                Process::PACKED_REJECT,
            ];
            if (!empty($excludes)){
                foreach ($processes as $key => $value) {
                    if (in_array($value, $excludes))
                        unset($processes[$key]);
                }
            }
            return $processes;
        }
    }    
    /**
     * Define the required item return process to process item invididually
     * 
     * @return array
     */
    public function getItemReturnProcesses()
    {
        if ($this instanceof Item){
            return [
                Process::RETURNED_PENDING,
                Process::RETURNED_ACCEPT,
             ];
        }
    }       
    /**
     * Define the permitted item 1 step processing start processes 
     * 
     * @return array
     */
    public function getItem1StepStartProcesses()
    {
        if ($this instanceof Item){
            return array(
                Process::ORDERED,
                Process::DEFERRED,
            );
        }
    }      
    /**
     * Define the permitted item 3 steps processing interim processes 
     * 
     * @return array
     */
    public function getItemInProcessing()
    {
        if ($this instanceof Item){
            return [
                Process::PICKED_ACCEPT,
                Process::DEFERRED_PICKED_ACCEPT,
                Process::PICKED_REJECT,
                Process::PACKED_ACCEPT,
                Process::DEFERRED_PACKED_ACCEPT,
                Process::PACKED_REJECT,
            ];
        }
    }     
    /**
     * Define the permitted item 3 steps processing actions  
     * 
     * @return array
     */
    public function get3StepsItemProcessingActions()
    {
        return [
            WorkflowManager::ACTION_PICK,
            WorkflowManager::ACTION_PACK,
            WorkflowManager::ACTION_SHIP,
        ];
    }  
    
    public function paid() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_paidCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function unpaid() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_deferredCondition(),'OR');
        $criteria->addCondition($this->_unpaidCondition(),'OR');
        return $this->mergeWithCriteria($criteria);
    }
    public function deferred() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_deferredCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function ordered() 
    {
        $criteria=new CDbCriteria(); 
        if ($this instanceof ShippingOrder){
            $criteria->addCondition($this->_deferredCondition(),'OR');
            $criteria->addCondition($this->_orderedCondition(),'OR');
        }
        else
            $criteria->addCondition($this->_orderedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function cancelled() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_cancelledCondition(),'OR');
        $criteria->addCondition($this->_deferredCancelledCondition(),'OR');
        return $this->mergeWithCriteria($criteria);
    }
    public function rejected() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_rejectedCondition());
        return $this->mergeWithCriteria($criteria);
    }    
    public function processed() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_processedCondition());
        return $this->mergeWithCriteria($criteria);
    }        
    public function fulfilled() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_fulfilledCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function partial_fulfilled() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_partialFulfilledCondition());
        return $this->mergeWithCriteria($criteria);
    }    
    public function picked() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_pickedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function packed() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_packedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function shipped() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_shippedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function collected() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_collectedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function received() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_receivedCondition(),'OR');
        $criteria->addCondition($this->_collectedCondition(),'OR');
        $criteria->addCondition($this->_reviewedCondition(),'OR');
        return $this->mergeWithCriteria($criteria);
    }
    public function reviewed() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_reviewedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function returned() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_returnedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function refunded() 
    {
        $criteria=new CDbCriteria(); 
        $criteria->addCondition($this->_refundedCondition());
        return $this->mergeWithCriteria($criteria);
    }
    public function newOrder()
    {
        return $this->status==Process::COMPLETED;
    }

    public function newItem()
    {
        return $this->status==Process::COMPLETED;
    }

    public function orderOnHold()
    {
        return $this->status==Process::UNPAID || $this->status==Process::ORDER_REJECTED;
    }
    
    public function orderDeferred()
    {
        return $this->status==Process::DEFERRED;
    }

    public function orderPaid()
    {
        return $this->status==Process::PAID || $this->status==Process::ORDERED;
    }
    
    public function orderPendingVerified()
    {
        return $this->status==Process::PAID;
    }
    
    public function orderVerified()
    {
        return $this->status==Process::ORDERED || $this->status==Process::ORDER_REJECTED;
    }
    
    public function orderCancelled()
    {
        return $this->status===Process::CANCELLED || $this->status==Process::DEFERRED_CANCELLED;
    }
        
    public function orderConfirmed()
    {
        return $this->status==Process::ORDERED;
    }
    
    public function orderPartialFulfilled()
    {
        return $this->status==Process::ORDER_PARTIAL_FULFILLED;
    }
    
    public function orderFulfilled()
    {
        return $this->status==Process::ORDER_FULFILLED;
    }

    public function orderRefunded()
    {
        return $this->status==Process::REFUND;
    }
    
    public function payable()
    {
        return $this->status==Process::UNPAID;
    }
    
    public function repayable()
    {
        return $this->status==Process::ORDER_REJECTED;
    }
    
    public function receivable($user=null)
    {
        return ($this->status==Process::SHIPPED || $this->status==Process::COLLECTED) && 
                $this->account_id==(isset($user)?$user:user()->getId());
    }
    public function reviewable($user=null)
    {
        return $this->status==Process::RECEIVED && $this->account_id==(isset($user)?$user:user()->getId());
    }
    public function verifiable()
    {
        return $this->status==Process::PAID;
    }
    public function processable()
    {
        return $this->status==Process::ORDERED || $this->status==Process::DEFERRED || $this->status==Process::ORDER_PARTIAL_FULFILLED;
    }
    public function returnable()
    {
        return $this->status==Process::RETURNED_PENDING;
    }       
    
    public function shippable()
    {
        return (($this->status==Process::PACKED_ACCEPT ||$this->status==Process::DEFERRED_PACKED_ACCEPT) && $this->threeStepsWorkflow()) || 
               ($this->processable() && $this->oneStepWorkflow());
    }  	
    
    public function packable()
    {
        return  ($this->status==Process::PICKED_ACCEPT || $this->status==Process::DEFERRED_PICKED_ACCEPT) && $this->threeStepsWorkflow();
    }       
    public function pickable()
    {
        return ($this->status==Process::ORDERED || $this->status==Process::DEFERRED) && $this->threeStepsWorkflow();
    }
    public function outOfStock()
    {
        return $this->status==Process::PICKED_REJECT;
    }
    public function badQuality()
    {
        return $this->status==Process::PACKED_REJECT;
    }

    public function refundable()
    {
        if ($this instanceof Item){
            return $this->status==Process::RETURNED_ACCEPT || 
                  (!$this->skipWorkflow() && $this->hasShippingOrder && 
                     ($this->itemCancelled() || $this->outOfStock()|| $this->badQuality()));
        }
        elseif ($this instanceof ShippingOrder){
            $dataProvider = $this->searchItems();
            $count = 0;
            foreach ($dataProvider->data as $item){
                if ($item->refundable())
                    $count++;
            }
            return $count==$dataProvider->getTotalItemCount();
        }
        else
            return $this->orderCancelled();
    }    
    
    public function requireStockInfo()
    {
        return $this->processable() || $this->packable();
    }       
    
    public function itemDeferred()
    {
        return $this->status==Process::DEFERRED;
    }

    public function itemDeferredPicked()
    {
        return $this->status==Process::DEFERRED_PICKED_ACCEPT;
    }

    public function itemDeferredPacked()
    {
        return $this->status==Process::DEFERRED_PACKED_ACCEPT;
    }
    
    public function itemShipped()
    {
        return $this->status==Process::SHIPPED || $this->status==Process::COLLECTED;
    }
    
    public function itemCancelled()
    {
        return $this->status===Process::CANCELLED;
    }

    public function itemRefunded()
    {
        return $this->status==Process::REFUND;
    }
    public function itemReviewed()
    {
        return $this->status===Process::REVIEWED;
    }

    public function interruptable() {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'status=\''.Process::REVIEWED.'\'',
            'order'=>'create_time DESC',
        ));
        return $this;
    }     
    
    public function fulfillable()
    {
        if ($this instanceof Item){
            return ($this->status==Process::SHIPPED 
                   || $this->status==Process::COLLECTED 
                   || $this->status==Process::RECEIVED 
                   || $this->status==Process::REVIEWED);
        }
        if ($this instanceof ShippingOrder){
            $dataProvider = $this->searchItems();
            $count = 0;
            foreach ($dataProvider->data as $item){
                if ($item->fulfillable())
                    $count++;
            }
            return $count==$dataProvider->getTotalItemCount();
        }
        //undefined
        throwError400(Sii::t('sii','Bad request'));
    }
    
    public function cancellable()
    {
        if ($this instanceof Item){
            if ($this->oneStepWorkflow())
                return $this->processable();
            elseif ($this->threeStepsWorkflow())
                return $this->pickable();
            else
                return $this->processable() || $this->payable();
        }
        if ($this instanceof ShippingOrder){
            $dataProvider = $this->searchItems();
            $count = 0;
            foreach ($dataProvider->data as $item){
                if ($item->cancellable())
                    $count++;
            }
            return $count==$dataProvider->getTotalItemCount();
        }
        if ($this instanceof Order) 
            return $this->processable();
        //undefined
        throwError400(Sii::t('sii','Bad request'));
    }
    /**
     * For now only Item with certain status support undo
     * @return boolean
     */
    public function undoable()
    {
        if ($this instanceof Item){
            if ($this->shippingOrder!=null){
                return $this->shippingOrder->account_id== user()->getId() 
                       && $this->threeStepsWorkflow() //when item is picked
                       && !$this->shippingOrder->orderFulfilled()
                       && !$this->itemShipped() //when item is shipped/collected rollback not supported
                       && ($this->packable() //when item is picked
                           || $this->shippable() //when item is packed
                           || $this->badQuality() 
                           //|| ($this->fulfillable() && !$this->shippingOrder->orderFulfilled()) //when item is shipped
                      );//fulfilled and cancelled order cannot undo item status
            }
            else
                return false;
        }
        return false;
    }        
    
    public function getAttachments()
    {
        return new CActiveDataProvider('Attachment',array(
                'criteria'=>array('condition'=>'obj_id=\''.$this->id.'\' and obj_type=\''.$this->tableName().'\''),
        ));
    } 
    /**
     * Check if skip item processing based on shop orders setting
     * @return boolean
     */
    public function skipWorkflow()
    {
        return $this->shop->skipOrdersItemProcessing();
    }
    /**
     * Check if skip item processing based on shop orders setting
     * @return boolean
     */
    public function oneStepWorkflow()
    {
        return $this->shop->oneStepOrdersItemProcessing();
    }
    /**
     * Check if skip item processing based on shop orders setting
     * @return boolean
     */
    public function threeStepsWorkflow()
    {
        return $this->shop->threeStepsOrdersItemProcessing();
    }    
    
}