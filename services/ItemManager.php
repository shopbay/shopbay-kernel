<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of ItemManager
 *
 * @author kwlok
 */
class ItemManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }   
    /**
     * Receive an item
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to receive
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function receive($user,$model,$transition)
    {
        $this->validate($user, $model, true);

        return $this->runWorkflow($user,
                                  $model, 
                                  $transition, 
                                  $transition->decision==WorkflowManager::DECISION_RETURN?Transition::SCENARIO_C1_C2_D:Transition::SCENARIO_C1_D, //scenario
                                  $transition->decision==WorkflowManager::DECISION_RETURN?Activity::EVENT_RETURN:Activity::EVENT_RECEIVE, 
                                  ['method'=>'receivable','param'=>$user]);
    }        
    /**
     * Review an item (it can be by decision review or return)
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to review
     * @param mixed comment CommentForm form contain review comment or $transition
     * @return CModel $model
     * @throws CException
     */
    public function review($user,$model,$data)
    {
        if (!($data instanceof CommentForm)){
            logError(__METHOD__.' Invalid form: '.get_class($data));
            throw new CException(Sii::t('sii','Invalid form'));
        }
            
        $this->validate($user, $model, true);

        if ($model->received()){

            $comment = new Comment();
            $comment->obj_type = SActiveRecord::restoreTablename($data->type);
            $comment->obj_id = $data->target;
            $comment->attributes = $data->getAttributes(array('content','rating'));
            if ($comment->rating==null)
                $comment->rating = 0;
            $comment->comment_by = $user;

            return $this->execute($model, array(
                        'saveReview'=>$comment,
                        self::WORKFLOW=>array(
                            'condition'=>array(Transition::MESSAGE=>array(
                                'Comments'=>$comment->htmlnl2br($comment->content),
                                'Rating'=>$comment->rating)),
                            'action'=>WorkflowManager::ACTION_REVIEW,
                            'decision'=>WorkflowManager::DECISION_NULL,
                            'saveTransition'=>true,
                            'transitionBy'=>$user,
                        ),
                        'recordActivity'=>array(
                            'event'=>Activity::EVENT_REVIEW,
                        ),
                    ));
        }
        else {
            logError(__METHOD__.' invalid model status',$model->getAttributes());
            throw new CException(Sii::t('sii','Invalid model status'));
        }
    }        
    /**
     * Pick an item
     * 
     * @param integer $user Session user id
     * @param CModel $model Item model to pick
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function pick($user,$model,$transition)
    {
        $model->setAccountOwner('shop');
        
        if ($transition->decision==WorkflowManager::DECISION_NOSTOCK)
            $activity = Activity::EVENT_REJECT;
        elseif ($transition->decision==WorkflowManager::DECISION_CANCEL)
            $activity = Activity::EVENT_CANCEL;
        else 
            $activity = Activity::EVENT_PICK;
        
        return $this->runWorkflow($user,
                                  $model, 
                                  $transition, 
                                  Transition::SCENARIO_C1_D, 
                                  $activity, 
                                  'pickable');
    }      
    /**
     * Pack an item
     * 
     * @param integer $user Session user id
     * @param CModel $model Item model to pack
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function pack($user,$model,$transition)
    {
        $model->setAccountOwner('shop');

        if ($transition->decision==WorkflowManager::DECISION_REJECT)
            $activity = Activity::EVENT_REJECT;
        elseif ($transition->decision==WorkflowManager::DECISION_CANCEL)
            $activity = Activity::EVENT_CANCEL;
        else 
            $activity = Activity::EVENT_PACK;
        
        return $this->runWorkflow($user,
                                  $model, 
                                  $transition, 
                                  Transition::SCENARIO_C1_D, 
                                  $activity, 
                                  'packable');
    }      
    /**
     * Ship an item
     * 
     * @param integer $user Session user id
     * @param CModel $model Item model to ship
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function ship($user,$model,$transition)
    {
        $model->setAccountOwner('shop');

        if ($transition->decision==WorkflowManager::DECISION_COLLECT){
            $scenario = $model->itemDeferredPacked()?WorkflowManager::ACTION_PAY:Transition::SCENARIO_C1_C2_D;//use as scenario
            $activity = Activity::EVENT_COLLECT;
        }
        elseif ($transition->decision==WorkflowManager::DECISION_CANCEL){
            $scenario = WorkflowManager::ACTION_PAY;
            $activity = Activity::EVENT_CANCEL;
        }
        else {
            $scenario = $model->itemDeferredPacked()?WorkflowManager::ACTION_PAY:WorkflowManager::ACTION_SHIP;//use as scenario
            $activity = Activity::EVENT_SHIP;
        }
        
        return $this->runWorkflow($user,$model,$transition,$scenario,$activity,'shippable');
    }      
    /**
     * Process an item (1 step processing - combine of pick, pack, ship)
     * 
     * @param integer $user Session user id
     * @param CModel $model Item model to ship
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function process($user,$model,$transition)
    {
        $model->setAccountOwner('shop');

        return $this->runWorkflow($user,
                                  $model, 
                                  $transition, 
                                  $model->itemDeferred()?WorkflowManager::ACTION_PAY:WorkflowManager::ACTION_SHIP, //scenario
                                  $transition->decision==WorkflowManager::DECISION_SHIP?Activity::EVENT_SHIP:Activity::EVENT_CANCEL, 
                                  'processable');
    }      
    /**
     * Return an item
     * 
     * @param integer $user Session user id
     * @param CModel $model Item model to ship
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function returnItem($user,$model,$transition)
    {
        $model->setAccountOwner('shop');

        return $this->runWorkflow($user,
                                  $model, 
                                  $transition, 
                                  $transition->decision==WorkflowManager::DECISION_ACCEPT?WorkflowManager::DECISION_RETURN:Transition::SCENARIO_C1_D, //scenario 
                                  $transition->decision==WorkflowManager::DECISION_ACCEPT?Activity::EVENT_ACCEPT:Activity::EVENT_REJECT, 
                                  'returnable');
    }     
    /**
     * Refund an item
     * 
     * @param integer $user Session user id
     * @param CModel $model Item model to ship
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function refund($user,$model,$transition)
    {
        $model->setAccountOwner('shop');

        return $this->runWorkflow($user,
                                  $model, 
                                  $transition, 
                                  WorkflowManager::ACTION_PAY, //scenario
                                  Activity::EVENT_REFUND, 
                                  'refundable');
    }     
    /**
     * Rollback an item
     * 
     * @param integer $user Session user id
     * @param CModel $model Item model to rollback
     * @return CModel $model
     * @throws CException
     */
    public function rollback($user,$model)
    {
        $model->setAccountOwner('shop');

        $this->validate($user, $model, true);
        
        return $this->execute($model, array(
            self::ROLLBACK=>array(
                'saveTransition'=>true,
                'transitionBy'=>$user,
            ),
            'recordActivity'=>array(
                'event'=>Activity::EVENT_ROLLBACK,
            )
        ));                   
        
    }      

}
