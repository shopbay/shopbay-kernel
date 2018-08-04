<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of OrderController
 *
 * @author kwlok
 */
class OrderController extends TaskBaseController 
{
    protected $modelType = 'Order';
    /**
     * Behaviors for this controller
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'rightsfilterbehavior' => [
                'class'=>'common.components.behaviors.RightsFilterBehavior',
                'whitelistActions'=>['pay'],
                'whitelistModels'=>[$this->modelType],
                'whitelistTasks'=>[
                    'Tasks.Order.Pay',
                    'Tasks.Item.Pay',
                ],               
            ],
        ]);
    }    
    /**
     * @return array action filters
     */
    public function filters()
    {
        $this->checkWhitelist(function(){
            if (isset($_POST['Transition']['obj_id']))
                return $this->loadModel($_POST['Transition']['obj_id'],$this->modelType);
            else
                return null;
        });
        return parent::filters();
    }    

    public function actionPay()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postPay();
        }
        else {
            $this->modelFilter = 'mine';//customer order
            $this->_httpGetInternal($this->getAction()->getId());
        }        
    }
 
    public function actionRepay()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postRepay();
        }
        else {
            $this->modelFilter = 'mine';//customer order
            $this->_httpGetInternal($this->getAction()->getId());
        }        
    }
    
    public function actionVerify()
    {
        if (Yii::app()->request->getIsPostRequest())
            $this->_postVerify();
        else 
            $this->_httpGetInternal($this->getAction()->getId());
    }
    
    private function _httpGetInternal($action)
    {
        $model=new Order($action);
        $model->unsetAttributes();  // clear any default values
        if (request()->getIsAjaxRequest() && isset($_GET['ajax'])) { //for purpose of filtering and pagination  
            header('Content-type: application/json');
            echo $this->search($model);
            Yii::app()->end();
        }
        $this->_process($action,$model);
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
        $criteria->compare('status',WorkflowManager::getProcessBeforeAction($model->tableName(), $model->getScenario()));
        $criteria->compare('order_no',$model->order_no,true);
        $criteria->compare('item_total',$model->item_total,true);
        $criteria->compare('item_shipping',$model->item_shipping,true);
        $criteria->compare('grand_total',$model->grand_total,true);
        
        $criteria = QueryHelper::prepareDatetimeCriteria($criteria, 'create_time', $model->create_time);

        return $criteria;
    }   
    
    private function _postPay()
    {
        $this->_workflow($this->modelType,'pay');
    }   
    
    private function _postRepay()
    {
        $this->_workflow($this->modelType,'repay');
    }   
    
    private function _postVerify()
    {
        $this->_workflow($this->modelType,'verify');
    }         
}