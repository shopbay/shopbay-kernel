<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of TaskBaseController
 *
 * @author kwlok
 */
abstract class TaskBaseController extends AuthenticatedController 
{
    protected $modelFilter = 'merchant';
    protected $modelSortOrder = 'create_time DESC';
   /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        $this->pageTitle = Sii::t('sii','Tasks');
    }

    protected function _getDataProvider($model)
    {
        $criteria = new CDbCriteria();
        $criteria->order = $this->modelSortOrder;
        $criteria->mergeWith($this->_getCriteria($model));

        $modelFilter = self::getTaskModelFilter($model->tableName(),$this->modelFilter);

        logTrace(__METHOD__.' criteria for model filter='.$modelFilter,$criteria);

        return new CActiveDataProvider(
                        $model->{$modelFilter}(),
                        array(
                            'criteria'=>$criteria,
                            'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                        ));
    }
    //child class should implement own criteria
    protected function _getCriteria($model)
    {
        return null;
    }
    
    protected function _process($action,$model)
    {
        user()->setFlash('hint',array(
            'message'=>TaskBaseController::getHint(get_class($model),$action, strtolower($model->displayName())),
            'type'=>'notice',
            'icon'=>'<i class="fa fa-lightbulb-o"></i>',
            'title'=>Sii::t('sii','Hint'),
        ));
        
        $this->render(strtolower(get_class($model)),
                array('action'=>$action,
                      'dataProvider'=>$this->_getDataProvider($model),
                      'searchModel'=>$model)
                );
    }
    
    protected function _workflow($type,$service)
    {
        if (isset($_POST['Transition'])){

            $transition = new Transition;
            $transition->attributes = $_POST['Transition'];
            logTrace(__METHOD__.' transition attributes', $transition->getAttributes());

            $model = $this->loadModel($transition->obj_id,$type);
                
            try {   

                $model = $this->module->getServiceManager($model)->{$service}(user()->isGuest?Account::GUEST:user()->getId(),$model,$transition);

                $message = Sii::t('sii','{object} is set to {status} successfully',array('{object}'=>$model->displayName(),'{status}'=>Process::getHtmlDisplayText($model->status)));
                user()->setFlash(get_class($model),array(
                            'message'=>$message,
                            'type'=>'success',
                            'title'=>Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($this->action->id)),'{model}'=>$model->displayName()))));
                
                logTrace(__METHOD__.' ok');

                header('Content-type: application/json');
                echo CJSON::encode(array(
                    'status'=>WorkflowManager::SUCCESS, 
                    'redirect'=>user()->hasRole(Role::ADMINISTRATOR)||user()->isSuperuser?request()->getUrlReferrer():$this->getWorkflowReturnUrl($model,user()->currentRole),
                ));
                Yii::app()->end();

            } catch(CException $e) {
                logError($e->getMessage(),array(),false,false);
                user()->setFlash(get_class($model),array(
                            'message'=>$e->getMessage(),
                            'type'=>'error',
                            'title'=>Sii::t('sii','{action} {model} {error}',array(
                                '{action}'=>isset($transition->decision)?Process::getDecisionText($transition->decision):Process::getActionText(ucfirst($this->action->id)),
                                '{model}'=>$model->displayName(),
                                '{error}'=>Sii::t('sii','Error')))));
                header('Content-type: application/json');
                echo CJSON::encode(array(
                        'status'=>WorkflowManager::FAILURE, 
                        'flash'=> $this->sflashWidget(get_class($model), true),
                        'form'=>$this->renderPartial($this->getModule()->getView('transitionform'),array(
                            'model'=>$model,
                            'transition'=>$transition,
                            'action'=>$this->action->id,
                            'decision'=>isset($transition->decision)?$transition->decision:WorkflowManager::DECISION_NULL),true),
                ));
                Yii::app()->end();
            }                    
        }
        throwError403(Sii::t('sii','Unauthorized Access'));        
    }    

    public function _rollback($model)
    {
        try {   
            $previousProcess = $model->status;
            
            $model = $this->module->getServiceManager($model)->rollback(user()->getId(),$model);

            $message = Sii::t('sii','Undo {object} from {process_from} to {process_to} successfully',array(
                        '{object}'=>$model->displayName(),
                        '{process_from}'=>Process::getHtmlDisplayText($previousProcess),
                        '{process_to}'=>Process::getHtmlDisplayText($model->status),
                    ));
            
            user()->setFlash(get_class($model),array('message'=>$message,
                                           'type'=>'success',
                                           'title'=>Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($this->action->id)),'{model}'=>$model->displayName()))));

            logTrace(__METHOD__.' ok');
            
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'status'=>WorkflowManager::SUCCESS, 
                'redirect'=>$this->getWorkflowReturnUrl($model,user()->currentRole),
            ));
            Yii::app()->end();

                
        } catch(CException $e) {
            logError($e->getMessage(),null,false);
            user()->setFlash(get_class($model),array(
                'message'=>$e->getMessage(),
                'type'=>'error',
                'title'=>Sii::t('sii','{action} {model} {error}',array(
                    '{action}'=>Process::getActionText(ucfirst($this->action->id)),
                    '{model}'=>$model->displayName(),
                    '{error}'=>Sii::t('sii','Error')))));
            header('Content-type: application/json');
            echo CJSON::encode(array(
                    'status'=>WorkflowManager::FAILURE, 
                    'flash'=> $this->sflashWidget(get_class($model), true),
            ));
            Yii::app()->end();
        }

    }
    /**
     * Return task group array data provider
     * @param type $role
     * @return \CArrayDataProvider
     */
    public static function getTaskGroupDataProvider($role)
    {
        $taskGroup = new CList();
        if ($role==Role::CUSTOMER){
            $taskGroup->add(Order::model()->tableName());
            $taskGroup->add(Item::model()->tableName());
        }
        if ($role==Role::MERCHANT){
            $taskGroup->add(Order::model()->tableName());
            $taskGroup->add(ShippingOrder::model()->tableName());
            $taskGroup->add(Item::model()->tableName());
            $taskGroup->add(Question::model()->tableName());
            $taskGroup->add(Shop::model()->tableName());
            $taskGroup->add(Product::model()->tableName());
            $taskGroup->add(Shipping::model()->tableName());
            $taskGroup->add(PaymentMethod::model()->tableName());
            $taskGroup->add(Tax::model()->tableName());
            $taskGroup->add(CampaignBga::model()->tableName());
            $taskGroup->add(CampaignPromocode::model()->tableName());
            $taskGroup->add(CampaignSale::model()->tableName());
            $taskGroup->add(News::model()->tableName());
            $taskGroup->add(Page::model()->tableName());
            $taskGroup->add(Media::model()->tableName());
            $taskGroup->add(Tutorial::model()->tableName());
        }
        if ($role==Role::ADMINISTRATOR){
            if (user()->hasRoleTask(Task::SHOPS_APPROVE_WORKFLOW))
                $taskGroup->add(Shop::model()->tableName());
            if (user()->hasRole(Role::TICKETS_MANAGER))
                $taskGroup->add(Ticket::model()->tableName());
            if (user()->hasRoleTask(Task::TUTORIALS_PUBLISH_WORKFLOW))
                $taskGroup->add(Tutorial::model()->tableName());
            if (user()->hasRoleTask(Task::TUTORIAL_SERIES_PUBLISH_WORKFLOW))
                $taskGroup->add(TutorialSeries::model()->tableName());
            if (user()->hasRoleTask(Task::PLANS_APPROVE_WORKFLOW))
                $taskGroup->add(Plan::model()->tableName());
            if (user()->hasRoleTask(Task::PACKAGES_APPROVE_WORKFLOW))
                $taskGroup->add(Package::model()->tableName());
        }
        
        return new CArrayDataProvider($taskGroup->toArray(), array(
            'keyField'=>false,
            'pagination'=>array(
                'pageSize'=>50,//need to set to higher value to have all task objects in one page
                'currentPage'=>0,//page 1
            ),
        ));        
    }   
    /**
     * Return task array data provider
     * @param type $role
     * @return \CArrayDataProvider
     */
    public static function getTaskDataProvider($role,$targetObject,$defaultModelFilter)
    {
        $tasks = new CMap();
        //[1] Get all workflow tasks
        foreach (WorkflowManager::getProcessDataProvider($role,$targetObject)->data as $workflow) {
            $type =  SActiveRecord::resolveTablename($workflow->obj_type);
            $count = self::getStatusCount($role, $workflow, self::getTaskModelFilter($workflow->obj_type,$defaultModelFilter));
            if ($count>0){
                $tasks->add($workflow->id,array(
                    'count'=>$count,
                    'object'=>$type::model()->displayName(),
                    'action'=>$workflow->action,
                    'actionText'=>Process::getActionText($workflow->action),
                    'actionUrl'=>$type::model()->getTaskUrl($workflow->parseAction()),
                ));
            }
        };
        //logTrace(__METHOD__.' tasks',$tasks->toArray());
        //aggreate counter for same action before return
        return new CArrayDataProvider(Helper::aggregateArrayValues($tasks, 'action', 'count'),array('keyField'=>false));
    }    
    /**
     * Make sure the correct model filter is returned according to model design / capability
     * @param type $obj_type
     * @return string
     */
    public static function getTaskModelFilter($obj_type,$defaultModelFilter)
    {
        if ($obj_type==Question::model()->tableName())
            return 'publicQuestion';
        elseif (in_array($obj_type,[
                    Package::model()->tableName(),
                    Plan::model()->tableName(),
                    Media::model()->tableName(),
                    Page::model()->tableName(),
                ]))
            return 'mine';
        elseif (in_array($obj_type,[
                    Tutorial::model()->tableName(),
                    TutorialSeries::model()->tableName(),
                    Ticket::model()->tableName(),
                ]))
            return 'submitted';
        else
            return $defaultModelFilter;
    }
    /**
     * Return reminder array data provider
     * 
     * @see self::getReminderDataArray
     * @param type $role
     * @return \CArrayDataProvider
     */
    public static function getReminderDataProvider($role,$modelFilter)
    {
        $taskGroup = new CList();
        $exclusion=array();
        if ($role==Role::CUSTOMER){
            $taskGroup->add(Order::model()->tableName());
            $taskGroup->add(Item::model()->tableName());
        }
        if ($role==Role::MERCHANT){
            $taskGroup->add(Order::model()->tableName());
            $taskGroup->add(ShippingOrder::model()->tableName());
            $taskGroup->add(Item::model()->tableName());
            $taskGroup->add(Question::model()->tableName());
            $exclusion = array(Question::model()->tableName()=>array(WorkflowManager::ACTION_ACTIVATE,WorkflowManager::ACTION_DEACTIVATE));
        }
        if ($role==Role::ADMINISTRATOR){
            if (user()->hasRoleTask(Task::SHOPS_APPROVE_WORKFLOW))
                $taskGroup->add(Shop::model()->tableName());
        }
        
        $reminder = array();
        foreach ($taskGroup->toArray() as $target) {
            $reminder = array_merge($reminder,self::getReminderDataArray($role, $target,$modelFilter,$exclusion));
        };
        
        return new CArrayDataProvider($reminder,array('keyField'=>false));
    }
    /**
     * Return reminder data array
     * @param type $role
     * @param type $targetObject
     * @return type
     */
    public static function getReminderDataArray($role,$targetObject,$modelFilter=null,$exclusion=array())
    {
        $reminder = new CMap();
        foreach (WorkflowManager::getProcessDataProvider($role,$targetObject,$exclusion)->data as $workflow) {
            $type =  SActiveRecord::resolveTablename($workflow->obj_type);
            $count = self::getStatusCount($role, $workflow, $modelFilter);
            if ($count>0){
                $reminder->add($workflow->id,array(
                    'count'=>$count,
                    'class'=>$type,
                    'object'=>$type::model()->displayName(),
                    'action'=>$workflow->action,
                    'actionLink'=>CHtml::link(Process::getActionText($workflow->action),$type::model()->getTaskUrl($workflow->parseAction())),
                ));
            }
        };
        //aggreate counter for same action before return
        return Helper::aggregateArrayValues($reminder, 'action', 'count');
    }
    /**
     * Return status count; Exclude auto-refund items (which will be handled by shipping order/ purchase order)
     * @param type $workflow
     * @param type $modelFilter
     * @return int
     */
    public static function getStatusCount($role,$workflow,$modelFilter=null)
    {
        $count = 0;
        $type =  SActiveRecord::resolveTablename($workflow->obj_type);
        if (isset($modelFilter)){
            if ($workflow->obj_type==Item::model()->tableName() && $role==Role::MERCHANT){
                if (in_array($workflow->start_process, Item::model()->getAutoRefundProcesses())){
                    logTrace(__METHOD__.' auto refund '.$workflow->start_process);//todo this applies to skipWorkflow only?
                    $count = 0;//exclude from counting
                }
                else {
                    //count item status shop by shop
                    //as some shops may have setting to skip item processing
                    foreach (Shop::model()->mine()->findAll() as $shop){
                        if (in_array($workflow->start_process,Item::model()->getItemReturnProcesses()))
                            $count += $type::model()->locateShop($shop->id)->countByStatus($workflow->start_process);
                        elseif ($shop->skipOrdersItemProcessing())
                            $count += 0;//exclude from counting
                        else if ($shop->oneStepOrdersItemProcessing()){
                            if (in_array($workflow->start_process, Item::model()->getItem1StepStartProcesses())
                                && $workflow->action!=WorkflowManager::ACTION_PICK)
                                $count += $type::model()->locateShop($shop->id)->countByStatus($workflow->start_process);
                        }
                        else
                            if ($workflow->action!=WorkflowManager::ACTION_PROCESS)
                                $count += $type::model()->locateShop($shop->id)->countByStatus($workflow->start_process);
                    }
                }
            }
            else 
                $count = $type::model()->{$modelFilter}()->countByStatus($workflow->start_process);
        }
        else {
            $count = $type::model()->countByStatus($workflow->start_process);
        }
        
        //logTrace(__METHOD__.' count  = '.$count, $workflow->getAttributes());
        return $count;
    }
    
    public static function getHint($class,$action,$object,$short=false)
    {
        if ($short)
            return Sii::t('sii','click on {icon} to {action}',array(
                        '{icon}'=> SButtonColumn::getButtonLabel($action),
                        '{action}'=>strtolower(Process::getActionText(ucfirst($action))),
                    ));
        else
            return Sii::t('sii','Click on {icon} to {action} {object}',array(
                        '{icon}'=> SButtonColumn::getButtonLabel($action),
                        '{action}'=>strtolower(Process::getActionText(ucfirst($action))),
                        '{object}'=>$object,
                    ));
    }
    
    public static function getWorkflowButtons($model)
    {
        $output = '';
        $buttonsMap = new CMap();//a way to make sure no same status button is displayed
        //[1] First show workflow button of model itself
        $workflowAction = $model->getWorkflowAction($model instanceof Item? user()->currentRole : null);
        $decisions = $model->getWorkflowDecisions($workflowAction);
        //logTrace(__METHOD__.' decisions',$decisions);
        if (is_array($decisions)){
            foreach ($decisions as $key => $decision) {
                if ($model->decisionable(user()->currentRole,$decision)){
                    $button = Yii::app()->controller->widget('zii.widgets.jui.CJuiButton',array(
                                'id'=>$decision.'-button',
                                'name'=>'worflow-button',
                                'buttonType'=>'button',
                                'caption'=>Process::getDecisionText($decision),
                                'value'=>$decision,
                                'htmlOptions'=>array(
                                    'data-id'=>$model->id,
                                    'data-type'=>get_class($model),
                                    'data-action'=>$workflowAction,
                                    'data-decision'=>$decision,
                                    'onclick'=>'qwByDecision($(this))',
                                    'class'=>'ui-button ui-widget ui-corner-all button-'.($key+1),
                                    'style'=>'background:'.Process::getColor($model->getNextStatus($workflowAction,$decision)).';',
                                )),true);
                    $buttonsMap->add($model->status.$decision,$button);
                }
            }
        }
        else {
            if ($model->actionable(user()->currentRole,user()->getId())){
                $button = Yii::app()->controller->widget('zii.widgets.jui.CJuiButton',array(
                                'id'=>$workflowAction.'-button',
                                'name'=>'worflow-button',
                                'buttonType'=>'button',
                                'caption'=>Process::getActionText($workflowAction),
                                'value'=>$workflowAction,
                                'htmlOptions'=>array(
                                    'data-id'=>$model->id,
                                    'data-type'=>get_class($model),
                                    'data-action'=>$workflowAction,
                                    'onclick'=>'qwByAction($(this))',
                                    'class'=>'ui-button ui-widget ui-corner-all primary-button',
                                    'style'=>'background:'.Process::getColor(WorkflowManager::getProcessAfterAction($model->tableName(), $workflowAction)).';',
                        )),true);
                $buttonsMap->add($model->status,$button);
            }
        }        
        
        //[2] Second show order items workflow button if any
        if ($model instanceof Order || $model instanceof ShippingOrder){
            foreach ($model->items as $item) {                
                if ($item->actionable(user()->currentRole,user()->getId()) && !$buttonsMap->contains($item->status)){
                    //use status key as unique to keep distinct buttons across items
                    $orderNo = $model instanceof ShippingOrder?$item->shipping_order_no:$item->order_no;
                    $action = $item->getWorkflowAction(user()->currentRole);
                    $button = Yii::app()->controller->widget('zii.widgets.jui.CJuiButton',array(
                                'id'=>$action.'-button',
                                'name'=>'worflow-button',
                                'buttonType'=>'button',
                                'caption'=>Process::getActionText($action),
                                'value'=>$action,
                                'htmlOptions'=>array(
                                    'data-action'=>$action,
                                    'data-id'=>$item->id,
                                    'data-type'=>get_class($item),
                                    'onclick'=>count($model->items)>1
                                                ?'redirect(\''.$item->getTaskUrl(Workflow::parseWorkflowAction($action)).'?order='.$orderNo.'\')'
                                                :'qwByAction($(this))',
                                    'class'=>'ui-button ui-widget ui-corner-all primary-button',
                                    'style'=>'background:'.Process::getColor(WorkflowManager::getProcessAfterAction($item->tableName(), $action)).';',
                        )),true);
                    $buttonsMap->add($item->status,$button);
                }
            }
        }
        //concantenate buttons
        foreach ($buttonsMap as $button)
            $output .= $button;
        return $output;
    }
    
    protected function getWorkflowReturnUrl($model,$role)
    {
        if ($model instanceof Order || $model instanceof Item){
            if ($role==Role::CUSTOMER && $model->byGuestCustomer())
                return $model->getGuestAccessUrl($model->shop->domain);
        }
        return $model->viewUrl;//default is viewUrl
    }
}