<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of TicketManager
 *
 * @author kwlok
 */
class TicketManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Check if session user has access to model object
     * 
     * @param integer $user
     * @param CActiveRecord $model
     * @param mixed $attribute Array or string. The attribute of model used to check access rights, 
     * @return boolean
     */
    public function checkObjectAccess($user,$model,$attribute=null)
    {
        if ((user()->isAdmin || user()->isSuperuser) && user()->getId()==$user)
            return true;//Admin will have access to ticket
        else       
            return parent::checkObjectAccess($user,$model,$attribute);
    } 
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to create
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::TICKET_DRAFT;//initial status is draft
        return $this->execute($model, array(
            'insertTicket'=>self::EMPTY_PARAMS,
            self::WORKFLOW=>array(
                'transitionBy'=>$user,
                'condition'=>Sii::t('sii','You have submitted this ticket.'),
                'action'=>WorkflowManager::ACTION_SUBMIT,
                'decision'=>WorkflowManager::DECISION_NULL,
                'saveTransition'=>true,
            ),
            'recordActivity'=>Activity::EVENT_CREATE,
        ));
    }
    /**
     * Reply model
     * 
     * @param integer $user Session user id
     * @param SFormModel $form form model to reply
     * @return CModel $model
     * @throws CException
     */
    public function reply($user,$form)
    {
        if (!($form instanceof TicketReplyForm))
            throw new CException(Sii::t('sii','Invalid form'));
        
        $model = new Ticket;
        $model->account_id = $user;
        $model->shop_id = $form->group;
        $model->subject = Ticket::REPLY_SUBJECT_PREFIX.$form->target;
        $model->content = $form->content;
        $model->status = Process::TICKET_REPLIED;

        $this->validate($user, $model, false);//validation include purify content
        
        $this->execute($model, array(
                        'insertReply'=>self::EMPTY_PARAMS,
                        self::NOTIFICATION=>self::EMPTY_PARAMS,
                    ));                   
        //recording reply activitiy
        $this->execute($form->ticket, array(
                        'recordActivity'=>array(
                            'event'=>Activity::EVENT_REPLY,
                            'description'=>$model->htmlnl2br($model->content),
                        ),
                    ));  
        
        if ($form->ticket->isClosed){
            $this->execute($form->ticket, array(
                self::WORKFLOW=>array(
                    'transitionBy'=>$user,
                    'condition'=>Sii::t('sii','You have reopened this ticket.'),
                    'action'=>WorkflowManager::ACTION_REOPEN,
                    'decision'=>WorkflowManager::DECISION_YES,
                    'saveTransition'=>true,
                ),
                'recordActivity'=>Activity::EVENT_REOPEN,
            ));        
        }
        return $model;
    }
    /**
     * Close ticket (This method is currently not used)
     * Ticket closure is done via TransitionAction
     * 
     * @param integer $user Session user id
     * @param CModel $model Ticket model to close
     * @param string $transition Contains the conditions and decision of this action (refer to s_workflow)
     * @return CModel $model
     * @throws CException
     */
    public function close($user,$model,$transition)
    {
        $this->validate($user, $model, false);
        return $this->execute($model, array(
            self::WORKFLOW=>array(
                'transitionBy'=>$user,
                'condition'=>Sii::t('sii','You have closed this ticket.'),
                'action'=>WorkflowManager::ACTION_CLOSE,
                'decision'=>WorkflowManager::DECISION_YES,
                'saveTransition'=>true,
            ),
            'recordActivity'=>Activity::EVENT_CLOSE,
        ));

    }      
    
}
