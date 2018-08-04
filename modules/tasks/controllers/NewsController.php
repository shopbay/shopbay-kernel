<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of NewsController
 *
 * @author kwlok
 */
class NewsController extends TransitionController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'News';
        $this->searchView = '_news';
        $this->messageKey = 'headline';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::NEWS_OFFLINE);
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::NEWS_ONLINE);

        $criteria->compare('id',$model->id);
        $criteria->compare('shop_id',$model->shop_id,true);
        $criteria->compare('headline',$model->headline,true);

        return $criteria;
    }          
}