<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SImageZoomer 
 * Note: This requires image zoom lib to work
 * 
 * @see extensions.elevatezoom
 * @author kwlok
 */
class SImageZoomer extends SWidget
{
    /**
     * The image zoomer library
     * @property string
     */
    protected $zoomer = 'common.extensions.elevatezoom.ElevateZoom';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.simagezoomer.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'simagezoomer';  
    /**
     * Default image owner model
     * @var CActiveRecord 
     */
    public $imageOwner;    
    /**
     * Default image version size
     * @var string 
     */
    public $defaultVersion = Image::VERSION_ORIGINAL;   
    /**
     * Default thumbnail version size
     * @var string 
     */
    public $thumbnailVersion = Image::VERSION_XXSMALL;  
    /*
     * Internal images construct according to zoomer format
     */
    private $_images = [];
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->constructImages();
        $this->render('index');
    }
    /**
     * Return if has image owner
     */
    protected function getHasImageOwner()
    {
        return isset($this->imageOwner);
    }
    /**
     * @return boolean Check if has multiple images
     */
    protected function getHasMultipleImages()
    {
        if ($this->hasImageOwner)
            return $this->imageOwner->hasMultipleImages;
        else
            return false;
    }    
    /**
     * Format: [
     *   'image1'=>[
     *     'default'=>true,//this default image to get first display, only one image is true
     *     'thumb'=>'thumbnail image url',
     *     'small'=>'default display image url',
     *     'large'=>'zoom in image url',
     *   ],
     *   ...
     *   'imageN'=>[
     *     'default'=>false,
     *     'thumb'=>'thumbnail image url',
     *     'small'=>'default display image url',
     *     'large'=>'zoom in image url',
     *   ]
     * ]
     * @see extensions.elevatezoom.ElevateZoom
     * @return array images 
     */
    public function getImages()
    {
        return $this->_images;
    }    
    /**
     * Contstruct images data
     */
    public function constructImages()
    {
        if ($this->hasMultipleImages){
            $images = $this->imageOwner->searchMediaAssociation()->data;
            $activeImages = [];
            $activeCount = 0;
            //need to separately compute activeImages due to we could have all images are external images and we only pick the first one as active
            foreach ($images as $key => $image){
                $activeImages[$key] = $image->id==$this->imageOwner->image;
                $activeCount += $activeImages[$key]==true?1:0;
            }
            if ($activeCount==count($activeImages)){//if all images are active, pick the first image as active
                foreach ($activeImages as $key => $active){
                    if ($key>0)
                        $activeImages[$key] = false;
                }
            }                
            foreach ($images as $key => $image){
                $this->_images[$image->id] = [
                    'active'=>$activeImages[$key],
                    'thumb'=>$image->isExternalImage?$image->getUrl():$image->createThumbnail($this->thumbnailVersion),
                    'small'=>$image->isExternalImage?$image->getUrl():$image->createThumbnail($this->defaultVersion),
                    'large'=>$image->getUrl(),
                    'alt'=>$this->imageOwner->displayLanguageValue('name',user()->getLocale()),
                ];
            }
        }
        else {
            $image = $this->imageOwner->loadMediaAssociationModel($this->imageOwner->image);
            if ($image!=null){
                $this->_images[$image->id] = [
                    'thumb'=>$image->isExternalImage?$image->getUrl():$image->createThumbnail($this->thumbnailVersion),
                    'small'=>$image->isExternalImage?$image->getUrl():$image->createThumbnail($this->defaultVersion),
                    'large'=>$image->getUrl(),
                    'alt'=>$this->imageOwner->displayLanguageValue('name',user()->getLocale()),
                ];                
            }
        }
        logTrace(__METHOD__,$this->_images);
    }
    /**
     * Default zoomer config
     * For mobile mode display, one example please refer to simagezoomer.js
     * 
     * @see extensions.elevatezoom.ElevateZoom
     * @return type
     */
    public function getConfig()
    {
        return [
            'galleryActiveClass'=>'active', 
            'cursor'=>'crosshair', //pointer
            'scrollZoom' => true,
            'zoomWindowFadeIn' => 500,
            'zoomWindowFadeOut' => 600,
            'zoomWindowWidth' => 500, 
            'zoomWindowHeight' => 400, 
            //'imageCrossfade'=>true, 
            'borderSize'=>1, 
            'loadingIcon'=>$this->getAssetsURL('common.assets.images').'/loading.gif', 
        ];
    }
    /**
     * Mobile config
     * If changes made, please also make at simagezoomer.js evelatezoomconfig_mobile()
     * 
     * @see extensions.elevatezoom.ElevateZoom
     * @return type
     */
    public function getMobileConfig()
    {
        $config = $this->getConfig();
        $config['zoomWindowWidth'] = 250;
        $config['zoomWindowHeight'] = 200;
        $config['zoomWindowPosition'] = 14;
            //'zoomType' => "lens",
            //'lensShape' => "round",
            //'lensSize' => 200,            
        return $config;
    }    
}
