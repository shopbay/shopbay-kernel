<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ProductController
 *
 * @author kwlok
 */
class ProductController extends TransitionController 
{
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Product';
        $this->searchView = '_products';
    }

    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;

        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::PRODUCT_OFFLINE);
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::PRODUCT_ONLINE);

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);
        $criteria->compare('unit_price',$model->unit_price,true);

        return $criteria;
    }        
}