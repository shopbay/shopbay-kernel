<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShippingController
 *
 * @author kwlok
 */
class ShippingController extends TransitionController 
{
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Shipping';
        $this->searchView = '_shippings';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::SHIPPING_OFFLINE);
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::SHIPPING_ONLINE);

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);
        $criteria->compare('method',$model->method,true);
        $criteria->compare('type',$model->type,true);

        return $criteria;
    }            
}