<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.tasks.components.TransitionControllerActionTrait');
/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends SPageIndexController
{
    use TransitionControllerActionTrait;
    
    protected $stateVariable = SActiveSession::MEDIA;
    protected $mediaUploadAction = 'upload';
    protected $mediaDownloadAction = 'download';
    protected $sessionActionsExclude = [];

    public function init()
    {
        parent::init();
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->modelType = 'Media';
        $this->viewName = Sii::t('sii','Media');
        $this->route = 'media/management/index';
        //$this->pageViewOption = SPageIndex::VIEW_GRID;
        $this->sortAttribute = 'update_time';
        //-----------------//
        $this->sessionActionsExclude = [
            $this->mediaUploadAction,
        ];
    }
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    protected function beforeAction($action)
    {
        $referer = request()->getUrlReferrer();
        $url = request()->getHostInfo().request()->getUrl();
        if ($referer != $url) {
            if (in_array($action->getId(), $this->sessionActionsExclude))
                logTrace(__METHOD__.' '.$action->getId() . ' excludes clearing from session');
            else {
                SActiveSession::clear($this->stateVariable);
                logTrace(__METHOD__.' '.$action->getId() . ' clear ' .$this->stateVariable);
            }
        }
        return true;
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array_merge(parent::actions(),$this->transitionActions(false,true,null,false),[
            'view'=>[
                'class'=>'common.components.actions.ReadAction',
                'model'=>$this->modelType,
            ],
            'delete'=>[
                'class'=>'common.components.actions.DeleteAction',
                'model'=>$this->modelType,
            ],
            $this->mediaUploadAction => [
                'class'=>'common.extensions.supload.actions.SUploadAction',
                'formClass'=>'MediaUploadForm',
                'stateVariable'=> $this->stateVariable,
                'secureFileNames'=>true,
                'path'=>Yii::app()->getBasePath()."/www/uploads",
                'publicPath'=>'/uploads',
            ],
            $this->mediaDownloadAction=>[
                'class'=>'common.components.actions.DownloadAction',
                'model'=>$this->modelType,
            ],        
            'assets'=>[
                'class'=>'common.modules.media.actions.AssetsAction',
                'modelFilter'=>'mine',
            ],        
        ]);
    }  
    /**
     * Create media action
     * Supports multiple media uploads
     * @throws Exception
     */
    public function actionCreate()
    {
        $this->pageTitle = Sii::t('sii','Upload Media');
        $model = new $this->modelType;
        $messages = new CList();
        
        if(isset($_POST[$this->modelType])){

            try {
                                
                if(SActiveSession::exists($this->stateVariable)) {
                    $files = SActiveSession::get($this->stateVariable);
                    foreach($files as $file){
                        if( is_file( $file["path"] ) ) {
                            
                            $config = [
                                'initialFilepath'=>$file['path'],
                                'name'=>$file['name'],
                                'filename'=>$file['filename'],
                                'mime_type'=>$file['mime'],
                                'size'=>$file['size'],
                            ];                            
                            $media = $this->module->serviceManager->create(user()->getId(),$config);
                            $messages->add(Sii::t('sii','{name} is uploaded successfully',['{name}'=>$media->name]));

                        } else {
                            //You can also throw an execption here to rollback the transaction
                            logWarning(__METHOD__.' '.$file["path"]." is not a file");
                            $messages->add(Sii::t('sii','{file} is not a file',['{file}'=>$file['path']]));
                        }
                    }
                    
                    user()->setFlash($this->modelType,[
                        'message'=>Helper::htmlList($messages),
                        'type'=>'success',
                        'title'=>Sii::t('sii','{model} Creation',['{model}'=>$model->displayName()]),
                    ]);
                    unset($_POST);
                    //clear session media files
                    SActiveSession::clear($this->stateVariable);

                    $this->redirect(url('media'));
                    Yii::app()->end();

                }

            } catch (CException $e) {
                logError(__METHOD__.' '.$e->getTraceAsString(), [], false);
                user()->setFlash(get_class($model),[
                    'message'=>$model->hasErrors()?Helper::htmlErrors($model->getErrors()):$e->getMessage(),
                    'type'=>'error',
                    'title'=>Sii::t('sii','Media Error'),
                ]);
            }
        }
        
        $this->render('create',['model'=>$model]);
        
    }  
    /**
     * Return page menu (with auto active class)
     * @param type $model
     * @return type
     */
    public function getPageMenu($model)
    {
        return array(
            array('id'=>'view','title'=>Sii::t('sii','View {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','view'),  'url'=>$model->viewUrl,'linkOptions'=>array('class'=>$this->action->id=='view'?'active':'')),
            array('id'=>'import','title'=>Sii::t('sii','Upload Media'),'subscript'=>Sii::t('sii','upload'), 'url'=>array('create')),
            array('id'=>'delete','title'=>Sii::t('sii','Delete {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','delete'), 'visible'=>$model->deletable(),
                    'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),
                                         'onclick'=>'$(\'.page-loader\').show();',
                                         'confirm'=>Sii::t('sii','Are you sure you want to delete this {object}?',array('{object}'=>strtolower($model->displayName()))))),
            array('id'=>'activate','title'=>Sii::t('sii','Activate {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','activate'), 'visible'=>$model->activable(), 
                    'linkOptions'=>array(
                        'submit'=>url('media/management/activate',array('Media[id]'=>$model->id)),
                        'onclick'=>'$(\'.page-loader\').show();',
                        'confirm'=>Sii::t('sii','Are you sure you want to activate this {object}?',array('{object}'=>strtolower($model->displayName()))),
                    )),
            array('id'=>'deactivate','title'=>Sii::t('sii','Deactivate {object}',array('{object}'=>$model->displayName())),'subscript'=>Sii::t('sii','deactivate'), 'visible'=>$model->deactivable(), 
                    'linkOptions'=>array(
                        'submit'=>url('media/management/deactivate',array('Media[id]'=>$model->id)),
                        'onclick'=>'$(\'.page-loader\').show();',
                        'confirm'=>Sii::t('sii','Are you sure you want to deactivate this {object}?',array('{object}'=>strtolower($model->displayName()))),
                )),
        );
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

    protected function getMediaUploadForm()
    {
        $mediaForm = new MediaUploadForm();
        $mediaForm->uploadRoute = url($this->module->id .'/'.$this->id .'/'.$this->mediaUploadAction);
        return $this->widget($mediaForm->uploadWidget, [
            'url' => $mediaForm->uploadRoute,
            'model' => $mediaForm,
            'attribute'=>$mediaForm->fileAttribute,
            'multiple' => true,
            'autoUpload'=>true,
            'uploadView'=>$mediaForm->uploadView,
            'downloadView'=>$mediaForm->downloadView,
            'formView'=>$mediaForm->formView,
            'options'=>[
                'previewMaxWidth'=>30,
                'previewMaxHeight'=>30,
                'maxNumberOfFiles'=>50,
                'progress'=>new CJavaScriptExpression('function(e, data){MediaUploadFormProgress($("#MediaUploadForm-form").fileupload("progress"));}'),
                'destroy'=>new CJavaScriptExpression('function (e, data) {console.log("destory-callback");'.$mediaForm->getDeleteButtonScript().'}'),
            ],
            'htmlOptions'=>[
                'class'=>$mediaForm->formClass,/*key identifier used at tasks.js*/
                'placeholder'=>'',
            ],
        ]);
    }   
    
}
