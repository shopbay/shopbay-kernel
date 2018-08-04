<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TaxController
 *
 * @author kwlok
 */
class TaxController extends TransitionController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Tax';
        $this->searchView = '_taxes';
        $this->messageKey = 'name';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::TAX_OFFLINE);
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::TAX_ONLINE);

        $criteria->compare('id',$model->id);
        $criteria->compare('shop_id',$model->shop_id,true);
        $criteria->compare('name',$model->name,true);

        return $criteria;
    }          
}