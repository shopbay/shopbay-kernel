<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CampaignPromocodeController
 *
 * @author kwlok
 */
class CampaignPromocodeController extends TransitionController 
{
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'CampaignPromocode';
        $this->searchView = '_campaigns';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;
        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::CAMPAIGN_OFFLINE);
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::CAMPAIGN_ONLINE);

        $criteria = QueryHelper::prepareDateCriteria($criteria, 'start_date', $model->start_date);
        $criteria = QueryHelper::prepareDateCriteria($criteria, 'end_date', $model->end_date);

        $criteria->compare('id',$model->id);
        $criteria->compare('code',$model->code,true);

        return $criteria;
    }             

}