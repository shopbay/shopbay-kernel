<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.simagemanager.SImageManager");
Yii::import("common.widgets.simagemanager.models.MultipleImagesForm");
Yii::import("common.widgets.simagemanager.models.SingleImageForm");
Yii::import("common.modules.media.models.SessionMedia");
/**
 * Description of MediaGallerySelectAction
 * NOTE: Currently support image only, and integrate with SImageManager widget components
 * 
 * @author kwlok
 */
class MediaGallerySelectAction extends CAction 
{
    /**
     * Name of the state variable the image object array is stored in
     * @var string
     */
    public $stateVariable = 'undefined';   
    /**
     * Image form model class name
     * @var string
     */
    public $formModel = SImageManager::MULTIPLE_IMAGES;  
    /**
     * Image view file
     * @var string
     */
    public $viewFile;  
    /**
     * Select media from media gallery and store into session repo 
     */
    public function run() 
    {
        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_GET['m'])) {
                
                logTrace(__METHOD__.' received $_GET[m]',$_GET['m']);
                $media = Media::model()->findByPk($_GET['m']);
                header('Content-type: application/json');
                
                if ($this->formModel==SImageManager::SINGLE_IMAGE)
                    $result = $this->saveSessionMediaSingleMode($media);
                else    
                    $result = $this->saveSessionMediaMultiMode($media);
                
                if (is_array($result)){//session repo is in array data structure
                    echo CJSON::encode(array(
                        'status'=>'success',
                        'html'=>$this->controller->renderPartial($this->getViewFile(),array(
                            'image'=>(object)$result,
                            'imageWidth'=>Image::VERSION_SMEDIUM,
                            'imageHeight'=>Image::VERSION_SMEDIUM,
                        ),true),
                    ));            
                }
                else {
                    echo CJSON::encode(array(
                        'status'=>'failure',
                        'message'=>$result,
                    ));            
                }
                Yii::app()->end();
            }
        }
        else
            throwError403(Sii::t('sii','Unauthorized Access'));
    }    

    /**
     * Now we need to save single media info to the user's session
     * [1] clear existing session image 
     * [2] set media into session
     * @return boolean
     */    
    protected function saveSessionMediaSingleMode($media) 
    {
        $validate = $this->validateMedia($media);
        if ($validate==Process::OK){
            //[1]clear existing session image
            SActiveSession::clear($this->stateVariable);        
            logTrace(__METHOD__.' previous session media cleared', SActiveSession::get($this->stateVariable));
            //[2]set media into session
            return $this->saveMedia($media);
        }
        else 
            return $validate;
    }    
    /**
     * Now we need to save media info to the user's session
     * [1] validate maximum image limit
     * [2] set media into session
     * @return boolean
     */
    protected function saveSessionMediaMultiMode($media) 
    {
        $validate = $this->validateMedia($media);
        if ($validate==Process::OK){
            //[1] check image limit
            $form = new MultipleImagesForm('limit');
            $form->stateVariable = $this->stateVariable;
            if (!$form->validate(array('stateVariable'))){
                logError(__METHOD__.' image limit hit', $form->errors);
                return $form->getError('stateVariable');
            }
            //[2]set media into session
            return $this->saveMedia($media);
        }
        else 
            return $validate;
    } 
    /**
     * Validate media 
     * @return boolean
     */
    protected function validateMedia($media)
    {
        //validate if media exists and against current user
        if ($media==null)
            return Sii::t('sii','Please select one media.');
        if (!$media->mine(user()->getId())->exists())
            return Sii::t('sii','Unauthorized Access');
        logTrace(__METHOD__.' media attributes',$media->attributes);
        
        return Process::OK;
    }
    /**
     * Save media into session repository
     * @param type $media
     * @return type
     */
    protected function saveMedia($media)
    {
        $sessionMedia = SessionMedia::updateRepository(
                    SActiveSession::get($this->stateVariable),
                    $media->getPreviewUrl(app()->urlManager->cdnDomain,request()->isSecureConnection),
                    $media               
                );

        SActiveSession::set($this->stateVariable, $sessionMedia);
        logTrace(__METHOD__.' media saved into session = '.$this->stateVariable,  SActiveSession::get($this->stateVariable));
        return $sessionMedia[$media->filename];
    }
    
    protected function getViewFile()
    {
        if (isset($this->viewFile))
            return $this->viewFile;
        elseif ($this->formModel==SImageManager::SINGLE_IMAGE)
            return 'common.widgets.simagemanager.views.singleimage._image';
        else // ($this->imageFormModel==SImageManager::MULTIPLE_IMAGES)
            return 'common.widgets.simagemanager.views._image';
    }    

}
