<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.simagemanager.SImageManager");
Yii::import("common.widgets.simagemanager.models.MultipleImagesForm");
Yii::import("common.widgets.simagemanager.models.SingleImageForm");
/**
 * Description of ImageAddByUrlAction
 *
 * @author kwlok
 */
class ImageAddByUrlAction extends CAction 
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
    public $imageFormModel = SImageManager::MULTIPLE_IMAGES;  
    /**
     * Image view file
     * @var string
     */
    public $imageView;  
    /**
     * Add product image by url
     */
    public function run() 
    {
        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_GET['u'])) {
                header('Content-type: application/json');
                
                if ($this->imageFormModel==SImageManager::SINGLE_IMAGE)
                    $image = $this->_saveSingleImageIntoSession($_GET['u']);
                else    
                    $image = $this->_saveMultipleImagesIntoSession($_GET['u']);
                
                if (is_array($image)){
                    echo CJSON::encode(array(
                        'status'=>'success',
                        'html'=>$this->controller->renderPartial($this->_getImageView(),array(
                            'image'=>(object)$image,
                            'imageWidth'=>Image::VERSION_SMEDIUM,
                            'imageHeight'=>Image::VERSION_SMEDIUM,
                        ),true),
                    ));            
                }
                else {
                    echo CJSON::encode(array(
                        'status'=>'failure',
                        'message'=>$image,
                    ));            
                }
                Yii::app()->end();
            }
        }
        else
            throwError403(Sii::t('sii','Unauthorized Access'));
    }    
    /**
     * Now we need to save single image info to the user's session
     * [1] clear existing session image 
     * [2] set external image
     * @return boolean
     */    
    private function _saveSingleImageIntoSession($imageUrl) 
    {
        //[1]clear existing session image
        SActiveSession::clear($this->stateVariable);        
        logTrace(__METHOD__.' previous session image cleared', SActiveSession::get($this->stateVariable));
        //[2]set external image
        return $this->_saveExternalImage($imageUrl, 0);//always 0 as only one image
    }    
    /**
     * Now we need to save image info to the user's session
     * [1] validate maximum image limit
     * [2] set correct external image key
     * [3] validate correct image url
     * @return boolean
     */
    private function _saveMultipleImagesIntoSession($imageUrl) 
    {
        //[1] check image limit
        $form = new MultipleImagesForm('limit');
        $form->stateVariable = $this->stateVariable;
        if (!$form->validate(array('stateVariable'))){
            logError(__METHOD__.' image limit hit', $form->errors);
            return $form->getError('stateVariable');
        }
        //[2]check if any other external image exists and determine image key
        $imageKeys = new CMap();
        for ($i=0; $i < $form->uploadLimit; $i++) {//create image keys candidates (max at 30)
            $imageKeys->add($i,$i);
        }
        foreach (SActiveSession::get($this->stateVariable) as $imageName => $imageData) {
            if (substr($imageName, 0, strlen(Image::EXTERNAL_IMAGE))==Image::EXTERNAL_IMAGE){
                logTrace(__METHOD__.' remove image from $imageKeys... '.substr($imageName, strlen(Image::EXTERNAL_IMAGE)));
                $imageKeys->remove(substr($imageName, strlen(Image::EXTERNAL_IMAGE)+1));//lenght +1 to include the "." char in EXTERNAL_IMAGE
            }
        }
        //[3]set external image
        return $this->_saveExternalImage($imageUrl, min($imageKeys->getKeys()));
    }     
    
    private function _saveExternalImage($imageUrl,$imageKey)
    {
        $imageModel = new ImageExternal();
        $imageModel->setUrl($imageUrl);
        $imageModel->setImageKey($imageKey);
        //logTrace(__METHOD__.' image attributes',$imageModel->attributes);
        if ($imageModel->validate(array('src_url'))){
            $sessionImages = SingleImageForm::updateRepository(
                        SActiveSession::get($this->stateVariable),
                        null,
                        $imageUrl,
                        $imageModel               
                    );
            
            SActiveSession::set($this->stateVariable, $sessionImages);
            logTrace(__METHOD__.' image url saved into session = '.$this->stateVariable,  SActiveSession::get($this->stateVariable));
            return $sessionImages[$imageModel->filename];
        }
        else {
            logError(__METHOD__.' image url validation error', $imageModel->errors);
            return $imageModel->getError('src_url');
        }
    }
    
    private function _getImageView()
    {
        if (isset($this->imageView))
            return $this->imageView;
        elseif ($this->imageFormModel==SImageManager::SINGLE_IMAGE)
            return 'common.widgets.simagemanager.views.singleimage._image';
        else // ($this->imageFormModel==SImageManager::MULTIPLE_IMAGES)
            return 'common.widgets.simagemanager.views._image';
    }
}
