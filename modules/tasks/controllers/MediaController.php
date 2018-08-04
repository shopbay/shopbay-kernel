<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MediaController
 *
 * @author kwlok
 */
class MediaController extends TransitionController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Media';
        $this->searchView = '_media';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::MEDIA_OFFLINE);
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::MEDIA_ONLINE);

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);

        return $criteria;
    }          
}