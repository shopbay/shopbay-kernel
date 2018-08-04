<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageController
 *
 * @author kwlok
 */
class PageController extends TransitionController 
{
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Page';
        $this->searchView = '_pages';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::PAGE_OFFLINE);
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::PAGE_ONLINE);

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);

        return $criteria;
    }            
}