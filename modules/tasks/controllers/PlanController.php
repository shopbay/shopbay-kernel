<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of PlanController
 *
 * @author kwlok
 */
class PlanController extends TransitionController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Plan';
        $this->searchView = '_plans';
        $this->messageKey = 'name';
    }
    
    public function actionSubmit()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postSubmit();
        }
        else {
            $this->modelFilter = 'drafted';
            $model=new Plan($this->action->id);
            $model->unsetAttributes();  // clear any default values
            if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination  
                header('Content-type: application/json');
                echo $this->search($model);
                Yii::app()->end();
            }
            $this->_process($this->action->id,$model);
        }
    }       
    
    public function actionApprove()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postApprove();
        }
        else {
            $this->modelFilter = 'submitted';
            $model=new Plan($this->action->id);
            $model->unsetAttributes();  // clear any default values
            if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination
                    header('Content-type: application/json');
                    echo $this->search($model);
                    Yii::app()->end();
            }
            $this->_process($this->action->id,$model);
        }
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='approve')
            $criteria->compare('status',Process::PLAN_SUBMITTED);
        if ($model->getScenario()=='submit')
            $criteria->compare('status',Process::PLAN_DRAFT);

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);

        return $criteria;
    }    
    
    private function _postApprove()
    {
        Yii::import('common.components.actions.api.ApiWorkflowAction');
        $action = new ApiWorkflowAction($this,__METHOD__);
        $action->apiRoute = '/plans';
        $action->model = $this->modelType;
        $action->transitionAction = 'approve';
        $action->transitionConditionMap = [
            'condition1'=>'reason',
            'condition2'=>'remarks',
        ];
        $this->runAction($action);
    }       
    
    private function _postSubmit()
    {
        $this->_workflow('Plan','submit');
    }      
}