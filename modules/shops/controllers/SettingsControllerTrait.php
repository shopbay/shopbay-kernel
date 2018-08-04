<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SettingsControllerTrait
 *
 * @author kwlok
 */
trait SettingsControllerTrait 
{
   /**
     * Need an extra set of image actions to support Single images
     * @see ImageControllerTrait can only support either Single or Multiple image mode.
     * Default is multiple image mode
     */
    protected $faviconStateVariable;
    protected $faviconUploadAction = 'faviconupload';
    protected $faviconMediaGalleryFormGetAction = 'faviconmediagalleryformget';
    protected $faviconMediaGallerySelectAction = 'faviconmediagalleryselect';
     
    protected function faviconActions()
    {
        return [
            //separately support for favicon actions
            $this->faviconUploadAction => [
                'class'=>'common.widgets.simagemanager.actions.ImageUploadAction',
                'multipleImages'=>false,
                'stateVariable'=> $this->faviconStateVariable,
                'secureFileNames'=>true,
                'path'=>Yii::app()->getBasePath()."/www/uploads",
                'publicPath'=>'/uploads',
            ],
            $this->faviconMediaGalleryFormGetAction => [
                'class'=>'common.modules.media.actions.MediaGalleryFormGetAction',
                'formModel'=>SImageManager::SINGLE_IMAGE,
                'mediaGallerySelectAction'=>$this->faviconMediaGallerySelectAction,
            ],            
            $this->faviconMediaGallerySelectAction => [
                'class'=>'common.modules.media.actions.MediaGallerySelectAction',
                'formModel'=>SImageManager::SINGLE_IMAGE,
                'stateVariable' => $this->faviconStateVariable,
            ],    
        ];
    }
    
    protected function getSettingsSectionsData($model) 
    {
        $sections = new CList();
        if ($model!=null){
            $count=0;
            foreach($model->getList() as $attribute){
                $settings = $model->getForm($attribute)->displaySettings();
                $sections->add(array('id'=>$attribute,
                         'name'=>$model->getAttributeLabel($attribute),
                         'heading'=>true,'top'=>$count==0?true:false,
                         'html'=>$this->widget('common.widgets.SDetailView', array(
                            'data'=>$model,
                            'columns'=>array($settings),
                        ),true)));
                $count++;
            }
        }
        return $sections->toArray();
    }      
    
    protected function _actionInternal($form,$setting,$values=null,$script=null) 
    {
        $ownerModel = $this->getOwnerModel();

        $this->pageTitle = $this->baseSettingsform->displayName().' | '.$ownerModel->displayLanguageValue('name',user()->getLocale());
        
        if (isset($values)){
            
            $this->saveSettings($ownerModel, $form, $setting, $values);
            
        }
        else if (isset($_POST[get_class($form)])){
            
            $this->saveSettings($ownerModel, $form, $setting, $_POST[get_class($form)]);
            
            unset($_POST);        
        }

        $this->render('template',array('model'=>$ownerModel,'setting'=>$setting,'script'=>$script));
        
    }
    
    public function saveSettings($ownerModel,$form,$attribute, $values)
    {
        try {
            $form->attributes = $values;
            $form->{$this->baseSettingsform->ownerAttribute} = $ownerModel->id;

            if (!$form->validate(array_keys($values)))
                throw new CException(Helper::htmlErrors($form->getErrors()));

            $currentSettings = json_decode($ownerModel->settings->$attribute,true);
            if ($currentSettings==null){
                logTrace(__METHOD__." $attribute setting is empty");
                $currentSettings = $form->attributes;//first time creation
            }
            else {
                foreach ($currentSettings as $key => $value) {//only override those received in $values
                    if (isset($values[$key])){
                        $currentSettings[$key] = $values[$key];
                        logTrace(__METHOD__." set $attribute setting $key to",$values[$key]);
                    }
                }
                foreach ($values as $key => $value) {//store those new setting not available in current settings
                    if (!isset($currentSettings[$key])){
                        $currentSettings[$key] = $value;
                        logTrace(__METHOD__." set $attribute setting $key to",$value);
                    }
                }
            }
            
            $ownerModel->settings->$attribute = json_encode($currentSettings);//json encode back

            $this->module->serviceManager->updateSettings(user()->getId(),$ownerModel->settings,$attribute);

            user()->setFlash(get_class($ownerModel),array(
                'message'=>Sii::t('sii','{model} is saved successfully.',array('{model}'=>$ownerModel->settings->displayName())),
                'type'=>'success',
                'title'=>Sii::t('sii','{model} Update',array('{model}'=>$ownerModel->settings->displayName()))));

        } catch (CException $e) {
            logError($this->modelType.' update error',$ownerModel->settings->getErrors());
            user()->setFlash(get_class($ownerModel),array(
                'message'=>$e->getMessage(),
                'type'=>'error',
                'title'=>Sii::t('sii','{model} Error',array('{model}'=>$ownerModel->settings->displayName()))));
        }
    }
    
    protected function getOwnerModel()
    {
        $search = current(array_keys($_GET));//take the first key as search attribute: owner slug
        logTrace(__METHOD__.' $_GET', $_GET);
        $model = $this->baseSettingsform->ownerClass;
        $ownerModel = $model::model()->findByAttributes(array('slug'=>$search));
        if ($ownerModel==null)
            throw new CException(Sii::t('sii','Owner Model not found'));
                
        if ($ownerModel->settings===null){
            $ownerModel->settings = new $this->modelType;
            $ownerModel->settings->{$this->baseSettingsform->ownerAttribute} = $ownerModel->id;
        }

        return $ownerModel;
    }
    
    public function renderFaviconForm($model)
    {
        $config = [
            'url'=>url($this->module->id .'/'.$this->id.'/'.$this->faviconUploadAction),
            'imageOwner'=>$model->owner->settingsModelInstance,
            'imageFormModel'=>SImageManager::SINGLE_IMAGE,
            'autoUpload'=>true,
            'formLabel'=>CHtml::label($model->getAttributeLabel('favicon'),'').Chtml::tag('span',array('class'=>'favicon-desc'),$model->getToolTip('favicon')),        
        ];
        if ($this->showMediaGallery())
            $config = array_merge($config,['mediaGalleryFormGetUrl'=>url($this->module->id .'/'.$this->id.'/'.$this->faviconMediaGalleryFormGetAction)]);
        
        $this->widget('common.widgets.simagemanager.SImageManager', $config);        
    }
    
    protected function getBaseSettingsForm()
    {
        return new BaseShopSettingsForm();
    }     
}
