<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends SPageIndexController 
{
    public function init()
    {
        parent::init();
        $missingModules = $this->getModule()->findMissingModules();
        if ($missingModules->getCount()>0)
            user()->setFlash($this->getId(),array('message'=>Helper::htmlList($missingModules),
                                            'type'=>'notice',
                                            'title'=>Sii::t('sii','Missing Module')));  
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'Comment';
        $this->viewName = Sii::t('sii','Comments');
        $this->route = 'comments/management/index';
        $this->sortAttribute = 'update_time';
        //-----------------//
        // Exclude following actions from rights filter 
        //-----------------
        $this->rightsFilterActionsExclude = [
            'create',
        ];
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),array(
            'view'=>array(
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
            ),  
            'update'=>array(
                'class'=>'common.components.actions.UpdateAction',
                'loadModelMethod'=>'prepareModel',
                'model'=>$this->modelType,
            ), 
            'delete'=>array(
                'class'=>'common.components.actions.DeleteAction',
                'model'=>$this->modelType,
            ),
        ));
    } 
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        if(isset($_POST['CommentForm'])) {
            
            $form = new CommentForm('create');
            $form->attributes = $_POST['CommentForm'];
            
            if (user()->isGuest){
                $type = $form->type;
                user()->returnUrl = $this->getLoginReturnUrl($type,$form->target);
                header('Content-type: application/json');
                user()->loginRequiredAjaxResponse = CJSON::encode(array('status'=>'loginrequired','url'=>user()->loginUrl));
                user()->loginRequired();
                Yii::app()->end();  
            }

            if($form->validate()){

                $model = new $this->modelType;;
                $model->obj_type = SActiveRecord::restoreTablename($form->type);
                $model->obj_id = $form->target;
                $model->content = $form->content;
                
                try {
                    $model = $this->module->getServiceManager()->create(user()->getId(),$model);
                    user()->setFlash($this->getId(),array(
                            'message'=>Sii::t('sii','{object} is posted successfully.',array('{object}'=>$this->modelType)),
                            'type'=>'success',
                            'title'=>Sii::t('sii','{object} Creation',array('{object}'=>$this->modelType)))); 
     
                    $form->unsetAttributes(array('content'));//keep obj_type and obj_id as need to echo back
                    $status = 'success';
                    $commentview = $this->renderPartial('_quickview',array('data'=>$model),true);

                } catch (CException $e) {
                    logError('ServiceManager create error: '.$e->getMessage());
                    user()->setFlash($this->getId(),array('message'=>$e->getMessage(),'type'=>'error','title'=>null)); 
                }
            }
            else
                logError('CommentForm validation error', $form->getErrors());

            header('Content-type: application/json');
            echo CJSON::encode(array(
                'status'=>isset($status)?$status:'failure',
                'form'=>$this->renderPartial('_quickform',array('model'=>$form),true),
                'comment'=>isset($commentview)?$commentview:null,
                'total'=>Sii::t('sii','n==1#{n} Comment|n>1#{n} Comments',array($form->retrieveCounter())),
            ));
            Yii::app()->end();  

        }
        throwError404(Sii::t('sii','The requested page does not exist'));

    }
    /**
     * Prepare model for update
     * @param type $id
     * @return \modelType
     */
    public function prepareModel($id=null)
    {
        if (isset($id)){//update action
            $model = $this->loadModel($id);
            $model->content = $model->htmlbr2nl($model->content);
        }
        return $model;
    }    
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        $filters = new CMap();
        $filters->add('all',Helper::htmlIndexFilter('All', false));
        return $filters->toArray();
    }    
}