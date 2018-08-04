<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShopController
 *
 * @author kwlok
 */
class ShopController extends TransitionController 
{
    protected $formType = 'ShopApplicationForm';        
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->modelType = 'Shop';
        $this->searchView = '_shops';
    }

    public function actionApprove()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postApprove();
        }
        else {
            $this->modelFilter = 'all';
            $model=new Shop($this->action->id);
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
            $criteria->compare('status',Process::SHOP_PENDING,false,'OR');
        else 
            $criteria->compare('status',Process::SHOP_APPROVED);

        if ($model->getScenario()=='activate')
            $criteria->compare('status',Process::SHOP_OFFLINE,false,'OR');
        if ($model->getScenario()=='deactivate')
            $criteria->compare('status',Process::SHOP_ONLINE,false,'OR');

        $criteria->compare('id',$model->id);
        $criteria->compare('name',$model->name,true);
        $criteria->compare('tagline',$model->tagline,true);

        return $criteria;
    }   
    private function _postApprove()
    {
        $this->_workflow('Shop','approve');
    }       
}