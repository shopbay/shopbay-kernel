<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.tasks.controllers.TutorialController');
/**
 * Description of TutorialSeriesController
 *
 * @author kwlok
 */
class TutorialSeriesController extends TutorialController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'TutorialSeries';
        $this->searchView = '_tutorialseries';
        $this->messageKey = 'name';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='publish')
            $criteria->compare('status',Process::TUTORIAL_SERIES_SUBMITTED);
        if ($model->getScenario()=='submit')
            $criteria->compare('status',Process::TUTORIAL_SERIES_DRAFT);

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);

        return $criteria;
    }    
   
}