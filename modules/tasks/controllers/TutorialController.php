<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TutorialController
 *
 * @author kwlok
 */
class TutorialController extends TransitionController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Tutorial';
        $this->searchView = '_tutorials';
    }
    
    public function actionSubmit()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postSubmit();
        }
        else {
            $this->modelFilter = 'drafted';
            $model=new $this->modelType($this->action->id);
            $model->unsetAttributes();  // clear any default values
            if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination
                header('Content-type: application/json');
                echo $this->search($model);
                Yii::app()->end();
            }
            $this->_process($this->action->id,$model);
        }
    }
    
    public function actionPublish()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postPublish();
        }
        else {
            $this->modelFilter = 'submitted';
            $model=new $this->modelType($this->action->id);
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

        if ($model->getScenario()=='publish')
            $criteria->compare('status',Process::TUTORIAL_SUBMITTED);
        if ($model->getScenario()=='submit')
            $criteria->compare('status',Process::TUTORIAL_DRAFT);

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);

        return $criteria;
    }    
    
    private function _postPublish()
    {
        $this->_workflow($this->modelType,'publish');
    }       
    
    private function _postSubmit()
    {
        $this->_workflow($this->modelType,'submit');
    }       
    
}