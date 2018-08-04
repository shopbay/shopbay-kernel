<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.supload.models.SUploadForm");
/**
 * Description of SingleImageForm
 *
 * @author kwlok
 */
class SingleImageForm extends SUploadForm 
{
    const UPLOAD_ACTION  = 'imageupload';
    /**
     * Configurable parameters
     */
    public $parent;//the parent model (e.g. Product, Shop etc that owns the image)
    public $uploadRoute;
    public $stateVariable = 'undefined';
    /**
     * Various views required parameters
     */
    public $uploadView   = 'common.widgets.simagemanager.views.singleimage.upload';
    public $downloadView = 'common.widgets.simagemanager.views.singleimage.download';
    public $formView     = 'common.widgets.simagemanager.views.singleimage.form';
    public $formClass    = 'upload-form';
    public $fileAttribute= 'file';
    /**
     * Initializes this model.
     */
    public function init()
    {
        parent::init();
        $this->maxSizeAllowed = Config::getSystemSetting('media_max_size');
        $this->mimeTypesAllowed = 'image/jpeg image/gif image/png image/tiff';
        $this->wrongMimeTypeMessage = Sii::t('sii','The file "{filename}" cannot be uploaded. Its MIME-type "{mime_type}" is not supported.',['{filename}'=>$this->name,'{mime_type}'=>$this->mime_type]);
    }     
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $this->wrongMimeTypeMessage = Sii::t('sii','The file "{filename}" is not an image. Its MIME-type is "{mime_type}".',['{filename}'=>$this->name,'{mime_type}'=>$this->mime_type]);
        return parent::rules();
    }   
    
    public function getErrorMessage()
    {
        $html = '<ul>';
        logTrace(__METHOD__, $this->getErrors());
        foreach ($this->getErrors() as $attribute => $error) {
             $html .= '<li>'.$this->getError($attribute).'</li>';
        }
        $html .= '</ul>';
        return $html;
    }
    public function getImageThumbnail($version=Image::VERSION_ORIGINAL)
    {
        return $this->parent->getImageThumbnail($version);
    }
    public function hasImage()
    {
        return $this->parent->hasImage();
    }    
    public function getParentId()
    {
        return $this->parent->id;
    }
    public function getLabel()
    {
        return $this->parent->label;
    }        
    /**
     * Update Image repo in its data structure
     * This is the data structure stored in session as well
     * 
     * @param type $repo
     * @param mixed $imageObj
     * @return type
     */
    public static function updateRepository($repo,$imagePath,$thumbnailUrl,$imageObj,$primary=false)
    {
        //load CSRF token
        $cookies = Yii::app()->request->getCookies();
        $cookieToken = $cookies->contains(Yii::app()->request->csrfTokenName)?$cookies->itemAt(Yii::app()->request->csrfTokenName)->value:'';
        //base structure
        $data = array(
            'primary' => $primary,                    
            'thumbnail_url' => $thumbnailUrl,
            'size' => $imageObj->size,
            'mime' => $imageObj->mime_type,
            'name' => $imageObj->name,
            'filename' => $imageObj->filename,
            'delete_type'=>'DELETE',
            'delete_url' => Yii::app()->getController()->createUrl(self::UPLOAD_ACTION).'?_method=delete&file='.$imageObj->filename.'&'.Yii::app()->request->csrfTokenName.'='.$cookieToken,
//            'delete_url' => Yii::app()->getController()->createUrl(self::UPLOAD_ACTION, array(
//                "_method" => "delete",
//                "file" => $imageObj->filename,
//                Yii::app()->request->csrfTokenName=>$cookieToken,                    
//            )),
        );
        
        if ($imageObj instanceof Image){
            $repo[$imageObj->filename] =  array_merge($data,array( 
                'id' => $imageObj->id,//only available for persistent image in DB
                'path' => $imageObj->name==Image::EXTERNAL_IMAGE?$imageObj->src_url:$imagePath.$imageObj->src_url,
                'url' => $imageObj->src_url,
            ));
        }
        elseif ($imageObj instanceof MediaAssociation){
            $repo[$imageObj->filename] =  array_merge($data,array( 
                'id' => $imageObj->id,//only available for persistent image in DB
                'path' => $imageObj->name==Image::EXTERNAL_IMAGE?$imageObj->src_url:$imageObj->filepath,
                'url' => $imageObj->src_url,
            ));
        }
        else {
            $repo[$imageObj->filename] = array_merge($data,array( 
                "path" => $imageObj->name==Image::EXTERNAL_IMAGE?$imageObj->src_url:$imagePath.$imageObj->filename,
                "url" => $thumbnailUrl,
            ));
        }    
        return $repo;
    }  
    
}