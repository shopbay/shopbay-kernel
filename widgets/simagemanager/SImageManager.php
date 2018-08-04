<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.supload.SUpload");
Yii::import("common.widgets.simagemanager.models.*");
/**
 * Description of SImageManager (CJuiInputWidget)
 * 
 * Support two modes:
 * [1] By upload (local image)
 *  1.1 window popup and select file to upload
 *  1.2 drag and drop (to be done...)
 * [2] By url
 * [3] Image editing  - crop, resize etc - check out open source library for integration 
 *
 * Note: Though it does not extends SWidget, but this also try to follow the SWidget methods and way of loading assets
 * 
 * @author kwlok
 */
class SImageManager extends SUpload
{
    const SINGLE_IMAGE    = 'SingleImageForm';
    const MULTIPLE_IMAGES = 'MultipleImagesForm';
    /**
     * string the id of the widget
     */
    public $id;
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.simagemanager.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'simagemanager';    
    /*
     * The image owner model (e.g. Product, Shop etc that owns the image)
     * @property CModel
     */
    public $imageOwner;
    /**
     * Image view file
     * @var string
     */
    public $imageView = '_image';  
    /**
     * Image form model class name
     * @var string
     */
    public $imageFormModel = self::MULTIPLE_IMAGES;  
    /**
     * Image dimension
     * @var string
     */
    public $imageWidth  = Image::VERSION_SMEDIUM;      
    public $imageHeight = Image::VERSION_SMEDIUM;     
    /**
     * ImageUrl form get url; Default to false, meaning url form is not displayed
     * To enable, this field needs to set the correct url to get the url form
     * e.g. url('/module/controller/'.$this->imageUrlFormGetAction)
     * @see common.widgets.simagemanager.actions.ImageUrlFormGetAction
     * @var string 
     */
    public $urlFormGetUrl  = false;
    /**
     * ImageUrl form script to call upon and passing in $urlFormGetUrl
     * @var string
     */
    public $urlFormScript = 'getimageurlform';
    /**
     * Media Gallery form; Default to false, meaning media gallery form is not displayed
     * To enable, this field needs to set the correct url to get the media gallery form
     * e.g. url('/module/controller/'.$this->mediaGalleryFormGetAction)
     * @see common.modules.media.actions.mediaGalleryFormGetAction
     * @var string 
     */
    public $mediaGalleryFormGetUrl  = false;
    /**
     * Media Gallery script to call upon and passing in $mediaGalleryFormGetUrl
     * @var string
     */
    public $mediaGalleryScript = 'getmediagalleryform';
    /**
     * Default upload limit
     * @var type 
     */
    public $uploadLimit;
    /**
     * Show form label
     * @var type 
     */
    public $showLabel = true;
    /**
     * Form label
     * @var type 
     */
    public $formLabel;
    /**
     * Empty text to display when no image
     * @var type 
     */
    public $emptyText;
    /**
     * Form view file for SingleImageForm
     * @var string 
     */
    public $singleFormView;
    public $singleDownloadView;
    public $singleUploadView;
    /**
     * Behaviors for this class
     */
    public function behaviors()
    {
        if ($this->pathAlias==null)
            throw new CException(Sii::t('sii','{class} must have path alias',['{class}'=>__CLASS__]));
        if ($this->assetName==null)
            throw new CException(Sii::t('sii','{class} must have asset name',['{class}'=>__CLASS__]));
        
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>$this->assetName,
                'pathAlias'=>$this->pathAlias,
            ],
        ];
    }    
    /**
     * Init widget
     * Attach AssetLoaderBehavior
     */ 
    public function init()
    {
        //if not informed will generate Yii defaut generated id, since version 1.6
        if(!isset($this->id))
            $this->id = $this->getId();
        
        $this->attachBehaviors($this->behaviors());
        //need to call init after attachBehaviors
        parent::init();
        
        $this->model = new $this->imageFormModel();
        $this->attribute = $this->model->fileAttribute;
        if (isset($this->imageOwner))
            $this->model->parent = $this->imageOwner;
        if (isset($this->uploadLimit) && $this->imageFormModel==self::MULTIPLE_IMAGES)
            $this->model->uploadLimit = $this->uploadLimit;
        
        $this->options = [
            'previewMaxWidth'=>30,
            'previewMaxHeight'=>30,
            'progress'=>new CJavaScriptExpression('function(e, data){'.$this->imageFormModel.'Progress($("#'.$this->formId.'").fileupload("progress"));}'),
        ];
        
        if ($this->imageFormModel==self::MULTIPLE_IMAGES){
            $this->options = array_merge($this->options,[
                'filesContainer'=>$this->model->filesContainer,
                'maxNumberOfFiles'=>$this->model->uploadLimit,
                'destroy'=>new CJavaScriptExpression('function (e, data) {console.log("destory-callback");'.$this->model->getDeleteButtonScript().'}'),
            ]);
        }
        
        $this->htmlOptions = [
            'class'=>$this->model->formClass,//key identifier used to select CSRF
            'accept'=>'image/*',
        ];
        
        if ($this->imageFormModel==self::SINGLE_IMAGE){
            $this->formView = isset($this->singleFormView) ? $this->singleFormView : 'singleimage/form';
            $this->downloadView = isset($this->singleDownloadView) ? $this->singleDownloadView : 'singleimage/download';
            $this->uploadView = isset($this->singleUploadView) ? $this->singleUploadView : 'singleimage/upload';
        }        
        
    }  
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        parent::run();
    }
    /**
     * Function to publish and register assets on page 
     * @throws CException
     */
    public function publishAssets()
    {
        $this->registerCssFile($this->pathAlias.DIRECTORY_SEPARATOR.'css',$this->assetName.'.css');
        $this->registerScriptFile($this->pathAlias.DIRECTORY_SEPARATOR.'js',$this->assetName.'.js');
        parent::publishAssets();
    }   
    
    public function getHasImages()
    {
        if ($this->imageOwner!=null)
            return $this->imageOwner->hasSessionImages();
        else
            return false;
    }
    /**
     * Render images
     * @return type
     */
    public function renderImages()
    {
        $output = '';
        foreach ($this->imageOwner->loadSessionMedia() as $image){
            $output .= $this->render($this->imageView,[
                'image'=>(object)$image,
                'imageWidth'=>$this->imageWidth,
                'imageHeight'=>$this->imageHeight,
            ],true);
        }    
        return $output;
    }   
    
    public function getFormLabel()
    {
        if ($this->showLabel){
            if (isset($this->formLabel))
                return $this->formLabel;
            else
                return CHtml::label(Sii::t('sii','1#Add Image|0#Choose Images', $this->multiple),'',['required'=>true]);
        }
        else
            return null;
    }
    
    public function getEmptyText()
    {
        if (!isset($this->emptyText)){
            $image = Image::model()->findByPk(Image::DEFAULT_IMAGE_PRODUCT);
            $this->emptyText = $image->render(Image::VERSION_XLMEDIUM);
//            $this->emptyText = Sii::t('sii','Please add image');
        }
        return $this->emptyText;
    }
    
    public function enableUrlForm()
    {
        return $this->urlFormGetUrl != false;
    }

    public function enableMediaGalleryForm()
    {
        return $this->mediaGalleryFormGetUrl != false;
    }
    
}
