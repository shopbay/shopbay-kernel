<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.tasks.views.workflow.sections.*');
/**
 * Description of WorkflowControler
 *
 * @author kwlok
 */
class WorkflowController extends TaskBaseController 
{ 
    /**
     * Behaviors for this controller
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'rightsfilterbehavior' => [
                'class'=>'common.components.behaviors.RightsFilterBehavior',
                'whitelistActions'=>['index'],
                'whitelistModels'=>['Order','Item'],              
            ],
        ]);
    }    
    /**
     * @return array action filters
     */
    public function filters()
    {
        $this->checkWhitelist(function(){
            if (isset($_POST['id']) && isset($_POST['type']) && in_array($_POST['type'],['Order','Item']))
                return $this->loadModel($_POST['id'],$_POST['type']);
            else
                return null;
        });
        return parent::filters();
    }    
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            'upload'=>array(
                'class'=>'AttachmentUploadAction',
                'formClass'=>'AttachmentForm',
                'stateVariable'=> SActiveSession::ATTACHMENT,
                'secureFileNames'=>true,
                'path'=>Yii::app()->getBasePath()."/www/uploads",
                'publicPath'=>'/uploads',
            ),
        );
    }    

    public function actionIndex()
    {
        if (request()->getIsAjaxRequest() && isset($_POST['id']) && isset($_POST['type']) && isset($_POST['action'])) {   
            $id = $_POST['id'];
            $type = $_POST['type'];
            $action = $_POST['action'];
            //[1] clear session attachment
            SActiveSession::clear(SActiveSession::ATTACHMENT);//previous upload is clear, have to redo
            //[2] get workflow model
            $model = $this->loadModel($id,$type);
            //[3] prepare any flash message
            $this->_prepareNoticeMessage($model);
            //[4] prepare model workflow setting
            $this->_prepareWorkflow($model);

            if ($this->module->getServiceManager($model)->checkObjectAccess(user()->getId(),$model,$model->isAdministerable()?'id':'account_id')){

                header('Content-type: application/json');
                //handle item redirect for review 
                if ( user()->currentRole==Role::CUSTOMER && $model instanceof Item && $action==WorkflowManager::ACTION_REVIEW){
                    echo CJSON::encode(array('redirect'=>$model->viewUrl));
                    Yii::app()->end();
                }
                
                if ($model instanceof Order || 
                    $model instanceof ShippingOrder || 
                    $model instanceof Item){
                    $workflowViewFile = strtolower($type);
                }
                else {//else default route to workflow form
                    $workflowViewFile = 'workflow';
                }
                
                echo CJSON::encode($this->renderPartial($workflowViewFile,
                        array('model'=>$model,
                              'action'=>$action,
                              'decision'=>isset($_POST['decision'])?$_POST['decision']:null,
                              'sections'=>$this->_getSectionsData($model)),
                        true));
                Yii::app()->end();
            }
            else {
                logError(__METHOD__.' No Access Right',$model->getAttributes());
                throwError403(Sii::t('sii','Unauthorized Access')); 
            }
        }
        throwError400(Sii::t('sii','We are sorry. Your request cannot be fulfilled due to bad syntax.'));
    }
    

    protected function _getSectionsData($model) 
    {
        $class = get_class($model).'Sections';
        $sections = new $class($this,$model);
        return $sections->data;
    }   

    protected function _prepareNoticeMessage($model)
    {
        if ($model instanceof ShippingOrder){
            if (!(   $model->verifiable() 
                  || $model->fulfillable() 
                  || $model->cancellable() 
                  || $model->refundable() 
                  || $model->skipWorkflow())
                ) {
                user()->setFlash(get_class($model),array(
                    'message'=>Sii::t('sii','Please process all purchased items first before fulfilling this order.'),
                    'type'=>'notice',
                    'title'=>Sii::t('sii','Shipping Order Notice')));
            }
        }        
    }
    
    protected function _prepareWorkflow($model)
    {
        if (user()->currentRole==Role::MERCHANT){
            if ($model instanceof Item || $model instanceof Order)
                $model->setAccountOwner('shop');
            elseif ($model instanceof Tutorial)
                $model->disableAdministerable();
        }            
        if (user()->hasRole(Role::ADMINISTRATOR)){
            $model->setOfficer(user()->getId());
            $model->setAccountOwner('officerAccount');
        }     
    }
        
    protected function _getAttachmentForm($model,$group,$decision=null) 
    {
        $attachmentForm = new AttachmentForm();
        $attachmentForm->uploadRoute = url("tasks/workflow/upload");
        $attachmentForm->obj_id = $model->id;
        $attachmentForm->obj_type = $model->tableName();
        $attachmentForm->group = $group;
        return $this->widget($attachmentForm->uploadWidget, array(
                    'url' => $attachmentForm->uploadRoute,
                    'model' => $attachmentForm,
                    'attribute'=>$attachmentForm->fileAttribute,
                    'multiple' => true,
                    'autoUpload'=>true,
                    'uploadView'=>$attachmentForm->uploadView,
                    'downloadView'=>$attachmentForm->downloadView,
                    'formView'=>$attachmentForm->formView,
                    'options'=>array('previewMaxWidth'=>30,'previewMaxHeight'=>30),
                    'htmlOptions'=>array('class'=>$attachmentForm->formClass,//key identifier used at tasks.js
                                         'placeholder'=>$model->getAttachmentPlaceholder($decision)),
        ));
    }   

    protected function _getTransitionForm($model,$action,$decision=null) 
    {
        $transition = new Transition();
        $transition->obj_id = $model->id;
        $transition->action = $action;
        $transition->decision = $decision;
        $this->renderPartial('_transitionform', array('model'=>$model,'transition'=>$transition));
    }   
    /**
     * Smart parsing of workflowable object name
     * @param type $model
     * @return type
     */
    protected function parseModelName($model)
    {
        if ($model->hasBehaviors('multilang'))
            return $model->displayLanguageValue('name',user()->getLocale());
        else
            return $model->name;
    }

}