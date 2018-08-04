<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ItemController
 *
 * @author kwlok
 */
class ItemController extends TaskBaseController 
{
    protected $modelType = 'Item';
    /**
     * Behaviors for this controller
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'rightsfilterbehavior' => [
                'class'=>'common.components.behaviors.RightsFilterBehavior',
                'whitelistActions'=>['receive'],
                'whitelistModels'=>[$this->modelType],
                'whitelistTasks'=>[
                    'Tasks.Item.Receive',
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
            if (isset($_POST[$this->modelType]['id']))
                return $this->loadModel($_POST[$this->modelType]['id'],$this->modelType);
            else
                return null;
        });
        return parent::filters();
    }    

    public function actionPick()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postPick();
        }
        else {
            $this->_actionInternal($this->getAction()->getId());
        }
    }
    public function actionPack()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postPack();
        }
        else {
            $this->_actionInternal($this->getAction()->getId());
        }
    }
    public function actionShip()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postShip();
        }
        else {
            $this->_actionInternal($this->getAction()->getId());
        }
    }
    /**
     * This is for 1 step process (combine of pick, pack and ship)
     */
    public function actionProcess()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postProcess();
        }
        else {
            $this->_actionInternal($this->getAction()->getId());
        }
    }
    public function actionReceive()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postReceive();
        }
        else {
            $this->modelFilter = 'mine';
            $this->getModule()->registerCssFile('common.modules.items.assets','items.css');
            $this->_actionCustomer($this->getAction()->getId());
        }
    }
    
    public function actionReview()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postReview();
        }
        else {
            $this->getModule()->registerRating();
            $this->getModule()->registerCssFile('common.modules.items.assets','items.css');
            $this->modelFilter = 'mine';
            $this->_actionCustomer($this->getAction()->getId());
        }
    }
    public function actionReturn()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postReturn();
        }
        else {
            $this->_actionInternal(WorkflowManager::ACTION_RETURNITEM);
        }
    }
    public function actionRefund()
    {
        if (Yii::app()->request->getIsPostRequest()){
            $this->_postRefund();
        }
        else {
            $this->_actionInternal($this->getAction()->getId());
        }
    }
    
    private function _actionCustomer($action)
    {
        $this->_setMode('customer');
        $this->_actionInternal($action);
    }        

    private function _actionInternal($action)
    {
        $model=new Item($action);
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['order'])){
            if (substr($_GET['order'], 0, 2)=='SO')
                $model->shipping_order_no = $_GET['order'];
            else
                $model->order_no = $_GET['order'];
        }
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

        return CJSON::encode($this->renderPartial('_items',
                                     array('dataProvider'=>$this->_getDataProvider($model),
                                           'searchModel'=>$model,
                                           'productIdInvisible'=>true,
                                           'orderNoVisible'=>true,
                                           'purchaserInfoInvisible'=>true),
                                     true));
    }    
    protected function _getCriteria($model)
    {
        logTrace(__METHOD__.' $model->getAttributes(), action='.$this->action->id,$model->getAttributes());

        $criteria=new CDbCriteria;

        //only include shops that are 3 steps workflow enabled.
        if (in_array(ucfirst($this->action->id), Item::model()->get3StepsItemProcessingActions())){
            $settings = ShopSetting::model()->merchant()->orderProcessing(ShopSetting::$itemProcess3Step)->findAll();
            $shopIds = [];
            foreach ($settings as $setting) {
                $shopIds[] = $setting->shop_id;
            }
            $criteria->addInCondition('shop_id',$shopIds);
        }
        
        if ($model->getScenario()=='refund') {
            $criteria->addNotInCondition('status',Item::model()->getAutoRefundProcesses());
        }

        $status = WorkflowManager::getProcessBeforeAction($model->tableName(), $model->getScenario());
        if (is_array($status)){
            $criteria->addInCondition('status',$status);
        }
        else {
            $criteria->compare('status',$status);
        }
        
        $criteria->compare('order_no',$model->order_no,true);
        $criteria->compare('shipping_order_no',$model->shipping_order_no,true);
        if (!empty($model->name))
            $criteria->addCondition('name like \'%'.$model->name.'%\' OR options like \'%'.$model->name.'%\'');
        $criteria->compare('unit_price',$model->unit_price,true);
        $criteria->compare('quantity',$model->quantity);
        $criteria->compare('shipping_surcharge',$model->shipping_surcharge,true);
        $criteria->compare('total_price',$model->total_price,true);

        $criteria = QueryHelper::prepareDatetimeCriteria($criteria, 'create_time', $model->create_time);

        return $criteria;
    }

    private $_mode = 'merchant';
    private function _setMode($mode)
    {
        return $this->_mode = $mode;
    }

    protected function getViewData($dataProvider,$searchModel)
    {
        $data = array('searchModel'=>$searchModel,
                      'dataProvider'=>$dataProvider,
                      'orderNoVisible'=>true,
                      'purchaserInfoInvisible'=>true);

        if ($this->_mode=='customer')
            return array_merge(array('customer'=>true),$data);

        return $data;
    }

    private function _postReceive()
    {
        $this->_workflow($this->modelType,'receive');
    }   
    
    private function _postReview()
    {
        if (isset($_POST['CommentForm'])){

            $commentForm = new CommentForm('create');
            $commentForm->attributes=$_POST['CommentForm'];

            $type  = $this->modelType;
            $model = $type::model()->mine()->findByPk($commentForm->src_id);

            try {   

                if ($model===null)
                    throw new CHttpException (404, Sii::t('sii','Item not found'));

                if(!$commentForm->validate())
                    throw new CHttpException(400, Helper::htmlErrors($commentForm->getErrors()));

                $model = $this->module->getServiceManager($model)->review(user()->getId(),$model,$commentForm);

                user()->setFlash(get_class($model),array(
                    'message'=>Sii::t('sii','We will continue to improve our services and server our customer the best. Have a nice day!'),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Thanks for your valuable review')));

                unset($_POST);

                $this->redirect($model->viewUrl);

                Yii::app()->end();

            } catch(Exception $e) {
                logError($e->getMessage(),$commentForm->getAttributes(),false);
                $model->setComment($commentForm);
                user()->setFlash(get_class($model),array('message'=>$e->getMessage(),
                               'type'=>'error',
                               'title'=>Sii::t('sii','Review Submission Error')));
                $this->render($this->getModule()->getView('item'), array('model'=>$model));
                Yii::app()->end();
            }

        }
        else
            $this->_workflow($this->modelType,'review');
    }   
    private function _postPick()
    {
        $this->_workflow($this->modelType,'pick');
    }   
    private function _postPack()
    {
        $this->_workflow($this->modelType,'pack');
    }       
    private function _postShip()
    {
        $this->_workflow($this->modelType,'ship');
    }   
    private function _postProcess()
    {
        $this->_workflow($this->modelType,'process');
    }     
    private function _postReturn()
    {
        $this->_workflow($this->modelType,'returnItem');
    }   
    private function _postRefund()
    {
        $this->_workflow($this->modelType,'refund');
    } 
    
    public function actionRollback()
    {
        if (Yii::app()->request->getIsPostRequest() && isset($_POST['id'])){
            $model = $this->loadModel($_POST['id'],$this->modelType);
            if ($model===null)
                throwError404();
            $this->_rollback($model);
        }
        throwError403(Sii::t('sii','Unauthorized Access'));
    }

    
}