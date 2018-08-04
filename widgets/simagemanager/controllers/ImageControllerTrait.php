<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.simagemanager.SImageManager');
/**
 * Description of ImageControllerTrait
 *
 * @author kwlok
 */
trait ImageControllerTrait 
{
    protected $sessionActionsExclude = [];
    protected $imageStateVariable;           
    protected $imageUploadAction = SessionMedia::UPLOAD_ACTION;
    protected $imageUrlFormGetAction = 'imageurlformget';
    protected $imageUrlAddAction = 'imageurladd';
    protected $mediaGalleryFormGetAction = 'mediagalleryformget';
    protected $mediaGallerySelectAction = 'mediagalleryselect';
    
    public function setSessionActionsExclude($more=[])
    {
        $this->sessionActionsExclude = array_merge([
            $this->imageUploadAction, 
            $this->imageUrlFormGetAction,
            $this->imageUrlAddAction,
            $this->mediaGalleryFormGetAction,
            $this->mediaGallerySelectAction,            
        ],$more);        
    }
    
    public function getRightsFilterImageActionsExclude($more=[])
    {
        return array_merge([
            $this->imageUrlFormGetAction,
            $this->imageUrlAddAction,            
            $this->mediaGalleryFormGetAction,
            $this->mediaGallerySelectAction, //todo should protect auth access?           
        ],$more);
    }
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    protected function runBeforeAction($action,$clearExtraStateVariable=null)
    {
        $referer = request()->getUrlReferrer();
        $url = request()->getHostInfo().request()->getUrl();
//        logTrace(__METHOD__.' Url '.$url.' vs referer',$referer);
        if ($referer!=$url){
            if (in_array($action->getId(), $this->sessionActionsExclude))
                logTrace(__METHOD__.' '.$action->getId() . ' excludes clearing from session');
            else {
                logTrace(__METHOD__.' clear '.$this->imageStateVariable);
                SActiveSession::clear($this->imageStateVariable);
                if (isset($clearExtraStateVariable)){
                    logTrace(__METHOD__.' clear '.$clearExtraStateVariable);
                    SActiveSession::clear($clearExtraStateVariable);
                }
            }
        }
        return true;      
    }    
    /**
     * Declares class-based actions.
     */
    public function imageActions($formModel=SImageManager::SINGLE_IMAGE,$uploadConfig=[],$exclude=[])
    {
        $actions = [
            $this->imageUploadAction => array_merge($uploadConfig,[
                'class'=>'common.widgets.simagemanager.actions.ImageUploadAction',
                'multipleImages'=>$formModel==SImageManager::MULTIPLE_IMAGES?true:false,
                'stateVariable'=> $this->imageStateVariable,
                'secureFileNames'=>true,
                'path'=>Yii::app()->getBasePath()."/www/uploads",
                'publicPath'=>'/uploads',
            ]),
            $this->imageUrlFormGetAction => [
                'class'=>'common.widgets.simagemanager.actions.ImageUrlFormGetAction',
                'formModel'=>$formModel,
                'addImageByUrlAction'=>$this->imageUrlAddAction,
            ],            
            $this->imageUrlAddAction => [
                'class'=>'common.widgets.simagemanager.actions.ImageAddByUrlAction',
                'imageFormModel'=>$formModel,
                'stateVariable' => $this->imageStateVariable,
            ],   
            $this->mediaGalleryFormGetAction => [
                'class'=>'common.modules.media.actions.MediaGalleryFormGetAction',
                'formModel'=>$formModel,
                'mediaGallerySelectAction'=>$this->mediaGallerySelectAction,
            ],            
            $this->mediaGallerySelectAction => [
                'class'=>'common.modules.media.actions.MediaGallerySelectAction',
                'formModel'=>$formModel,
                'stateVariable' => $this->imageStateVariable,
            ],    
        ];
        
        if (!empty($exclude)){
            foreach ($exclude as $action) {
                unset($actions[$action]);
            }
        }
        //logTrace(__METHOD__.' actions',$actions);
        return $actions;
    }
    
    public function renderImageForm($imageOwner,$formLabel=null,$url=null,$imageUrlForm=true)
    {
        $config = [
            'url'=>isset($url)?$url:url($this->module->id .'/'.$this->id.'/'.$this->imageUploadAction),
            'imageOwner'=>$imageOwner,
            'imageFormModel'=>SImageManager::SINGLE_IMAGE,
            'autoUpload'=>true,
        ];
        if ($imageUrlForm)
            $config = array_merge($config,['urlFormGetUrl'=>url($this->module->id .'/'.$this->id.'/'.$this->imageUrlFormGetAction)]);
        if ($this->showMediaGallery())
            $config = array_merge($config,['mediaGalleryFormGetUrl'=>url($this->module->id .'/'.$this->id.'/'.$this->mediaGalleryFormGetAction)]);
        if (isset($formLabel))
            $config = array_merge($config,['formLabel'=>$formLabel]);
        
        $this->widget('common.widgets.simagemanager.SImageManager', $config);        
    }
    
    public function renderMultiImagesForm($model,$uploadLimit,$imageUrlForm=true)
    {
        $config = [
            'url'=>url($this->module->id .'/'.$this->id.'/'.$this->imageUploadAction,array(
                'ptype'=>get_class($model->modelInstance),
                'pid'=>$model->id)),
            'imageOwner'=>$model->modelInstance,
            'uploadLimit'=>$uploadLimit,
            'multiple'=>true,
            'autoUpload'=>true,
        ];  
        if ($imageUrlForm)
            $config = array_merge($config,['urlFormGetUrl'=>url($this->module->id .'/'.$this->id.'/'.$this->imageUrlFormGetAction)]);
        if ($this->showMediaGallery())
            $config = array_merge($config,['mediaGalleryFormGetUrl'=>url($this->module->id .'/'.$this->id.'/'.$this->mediaGalleryFormGetAction)]);

        $this->widget('common.widgets.simagemanager.SImageManager', $config);        
    }    
    /**
     * Check if allowed to use media gallery for profile picture upload
     * @return type
     */
    protected function showMediaGallery()
    {
        return user()->hasRoleTask(Task::MEDIA);
    }
}
