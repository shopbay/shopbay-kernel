<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SImageViewer 
 * Note: This requires lib FancyBox to work and also Yii 1.1.15
 *
 * @author kwlok
 */
class SImageViewer extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.simageviewer.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'simageviewer';    
    /**
     * Default image model
     * @var string 
     */
    public $imageModel;    
    /**
     * Default image name; If null, it will use $imageModel->name
     * @var string 
     */
    public $imageName;    
    /**
     * Default image version size
     * @var string 
     */
    public $imageVersion = Image::VERSION_LARGE;    
    /**
     * Default image html options
     * @var string 
     */
    public $imageHtmlOptions = array();    
    /**
     * Default thumbnail version size
     * @var string 
     */
    public $thumbnailVersion = Image::VERSION_XXSMALL;    
    /**
     * Default image url to be displayed; When set, only this image will be used
     * However, $imageModel takes predence if $imageModel is set
     * 
     * @var string 
     */
    public $imageUrl;    
    /**
     * Default auto show thumbnail if more than one picture in gallery
     * @var string 
     */
    public $showThumbnail = true;    
    /**
     * Default image css class
     * @var string 
     */
    public $cssClass = 'gallery';    
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        if (Yii::getVersion()=='1.1.15')
            $this->registerFancybox();
    }    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->render('index');
    }
    /**
     * Return if has image model
     */
    protected function getHasImageModel()
    {
        return isset($this->imageModel);
    }
    /**
     * Return image name
     */
    protected function getImageName()
    {
        if (isset($this->imageName))
            return $this->imageName;
        else
            return $this->imageModel->name;
    }
    /**
     * Return image thumbnail
     */
    protected function getImageThumbnail($version=null,$alt=null)
    {
        if (!isset($version))
            $version = $this->imageVersion;
        if (!isset($alt))
            $alt = Sii::t('sii','Image');
        
        if ($this->hasImageModel)
            return $this->imageModel->getImageThumbnail($version,$this->imageHtmlOptions);
        elseif (isset($this->imageUrl)){
            $htmlOptions = $this->imageHtmlOptions;
            if ($version!=Image::VERSION_ORIGINAL)
                $htmlOptions = array_merge($htmlOptions,array('width'=>$version.'px'));
            return CHtml::image($this->imageUrl,$alt,$htmlOptions);
        }
        else
            return Yii::app()->image->loadModel(Image::DEFAULT_IMAGE)->render($version,$alt,$this->imageHtmlOptions);
    }
    /**
     * @return boolean Check if has multiple images
     */
    protected function getHasMultipleImages()
    {
        if ($this->hasImageModel)
            return $this->imageModel->getImagesCount() > 1;
        else
            return false;
    }
    
}
