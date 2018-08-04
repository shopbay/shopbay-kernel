<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShippingOrderController
 *
 * @author kwlok
 */
class ShippingOrderController extends TaskBaseController 
{
    protected $modelType = 'ShippingOrder';
    
    public function actionProcess()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postProcess();
        }
        else {
            $model=new ShippingOrder($this->getAction()->getId());
            $model->unsetAttributes();  // clear any default values
            if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination  
                    header('Content-type: application/json');
                    echo $this->search($model);
                    Yii::app()->end();
            }
            $this->_process($this->getAction()->getId(),$model);            
        }
    }
    public function actionRefund()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postRefund();
        }
        else {
            $model=new ShippingOrder($this->getAction()->getId());
            $model->unsetAttributes();  // clear any default values
            if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination  
                    header('Content-type: application/json');
                    echo $this->search($model);
                    Yii::app()->end();
            }
            $this->_process($this->getAction()->getId(),$model);
        }        
    }
    private function search($model)
    {
        if(isset($_GET[$this->modelType]))
            $model->attributes=$_GET[$this->modelType];

        return CJSON::encode($this->renderPartial('_'.strtolower(get_class($model)).'s',
                                     array('dataProvider'=>$this->_getDataProvider($model),
                                           'searchModel'=>$model),
                                     true));
    }  
    
    protected function _getCriteria($model)
    {
        $criteria=new CDbCriteria;
        $status = WorkflowManager::getProcessBeforeAction($model->tableName(), $model->getScenario());
        if (is_array($status)){
            $criteria->addInCondition('status',$status);
        }
        else {
            $criteria->compare('status',$status);
        }
        
        $criteria->compare('order_no',$model->order_no,true);
        $criteria->compare('item_total',$model->item_total,true);
        $criteria->compare('item_shipping',$model->item_shipping,true);
        $criteria->compare('grand_total',$model->grand_total,true);
        
        $criteria = QueryHelper::prepareDatetimeCriteria($criteria, 'create_time', $model->create_time);

        if (!empty($model->item_count))//used item_count as proxy to search by item names
            $criteria->addCondition($model->constructItemsInCondition($model->item_count,$this->modelFilter));

        //@todo support order total search
        //$criteria->compare('shop_id',$model->shop_id,true);//use shop_id as proxy for item name search

        return $criteria;
    }   
    
    private function _postProcess()
    {
        $this->_workflow($this->modelType,'process');
    }    
    private function _postRefund()
    {
        $this->_workflow($this->modelType,'refund');
    }    

}