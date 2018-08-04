<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TicketController
 *
 * @author kwlok
 */
class TicketController extends TransitionController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Ticket';
        $this->searchView = '_tickets';
        $this->messageKey = 'subject';
    }
    
    public function actionReply()
    {
        $this->modelFilter = 'submitted';
        $model = new Ticket($this->action->id);
        $model->unsetAttributes();  // clear any default values
        if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination
            header('Content-type: application/json');
            echo $this->search($model);
            Yii::app()->end();
        }
        $this->_process($this->action->id, $model);
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;
        $criteria->compare('status',Process::TICKET_SUBMITTED);
        $criteria->compare('id',$model->id);
        $criteria->compare('subject',$model->subject,true);

        return $criteria;
    }    

}