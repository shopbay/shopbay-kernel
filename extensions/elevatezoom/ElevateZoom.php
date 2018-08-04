<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ElevateZoom
 * Supports single image zoom or multiple images in gallery
 * 
 * @author kwlok
 */
class ElevateZoom extends CWidget
{
    private $_baseurl;
    /*
     * @string The id of the widget
     */
    public $id;
    /*
     * @array of config settings 
     */
    public $config=[];
    /*
     * @array of image settings
     * Format: 
     * [
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
     */
    public $images=[];
    /**
     * Default image version size
     * @var string 
     */
    public $smallVersion = 360;   
    /**
     * Default thumbnail version size
     * @var string 
     */
    public $thumbVersion = 30;  
    /**
     * Init widget
     */
    public function init() 
    {        
        parent::init();
        if(!isset($this->id))
            $this->id = 'ez_'.$this->getId();
        // publish the required assets
        $this->publishAssets();
        
        //-----------
        //Comment off below to run example (choose one)
        //-----------
        //$this->getExampleData();
        //$this->getGalleryExampleData();
    }
    /**
     * Publish assets
     * @throws Exception
     */
    public function publishAssets()
    {
        $assets = dirname(__FILE__).'/assets';
        $this->_baseurl = Yii::app()->assetManager->publish($assets);
        if(is_dir($assets)){
            Yii::app()->clientScript->registerCSSFile($this->_baseurl.'/css/elevatezoom-custom.css');
            Yii::app()->clientScript->registerScriptFile($this->_baseurl.'/js/jquery.elevateZoom-3.0.8.min.js', CClientScript::POS_HEAD);
        } else {
            throw new CException(__CLASS__.' Error: Could not find assets to publish.');
        }
    }
    /**
     * Run widget
     */
    public function run()
    {
        if ($this->hasGallery)
            $this->config['gallery'] = $this->galleryId;
        $config = CJavaScript::encode($this->config);
        Yii::app()->clientScript->registerScript($this->getId(), "$('#$this->id').elevateZoom($config);");
        $this->render('index');
    }
    /**
     * This is the default active image (zoomable)
     * @return type
     */
    protected function renderActiveImage()
    {
        if(!isset($this->images) || empty($this->images))
            throw new CException(__CLASS__.' Error: Missing images.');
        
        $activeImage = null;
        if (count($this->images)==1){
            foreach ($this->images as $image) {
                $activeImage = $image;
                break;//the first and only one image
            }
        }
        else {//multiple images
            foreach ($this->images as $image) {
                if (isset($image['active']) && $image['active'])
                    $activeImage = $image;
            }
        }
        echo CHtml::image($activeImage['small'],$activeImage['alt'], array('id'=>$this->id,'width'=>$this->smallVersion,'class'=>'elevatezoom-image','data-zoom-image'=>$activeImage['large']));
    }    
    
    protected function renderGallery()
    {
        $html = CHtml::openTag('div',array('id'=>$this->galleryId,'class'=>'gallery'));
        foreach ($this->images as $image) {
            $html .= $this->getGalleryImageElement($image);
        }
        $html .= CHtml::closeTag('div');
        echo $html;
    }
    
    protected function getGalleryImageElement($image)
    {
        $element = CHtml::openTag('a',array(
            'href'=>'#',
            'data-image'=>$image['small'],
            'data-zoom-image'=>$image['large'],
            'class'=>isset($image['active'])&&$image['active']?'active':''));
        $element .= CHtml::image($image['thumb'], $image['alt'], array('id'=>$this->id,'width'=>$this->thumbVersion));
        $element .= CHtml::closeTag('a');
        return $element;
    }
    /**
     * Built-in example images
     * @param type $image
     * @param type $version
     * @return type
     */
    protected function getImageUrl($image,$version='small')
    {
        return $this->_baseurl.'/images/'.$version.'/'.$image;
    }
    /**
     * @return boolean Check if there is gallery. Single image will not form gallery
     */
    public function getHasGallery()
    {
        return count($this->images) > 1;
    }
    /**
     * @return string Gallery id
     */
    protected function getGalleryId()
    {
        return $this->id.'_gallery';
    }
    /**
     * Get example data (one image zoom)
     */
    public function getExampleData()
    {
        $this->config = [
            'zoomType' => 'inner',
            'scrollZoom' => true,
            'cursor'=>'crosshair', //pointer
            'zoomWindowFadeIn' => 500,
            'zoomWindowFadeOut' => 750,
            'borderSize'=>0, 
        ];        
        $this->images = [
            'image1'=>[
                'thumb'=>$this->getImageUrl('image1.jpg','thumb'),
                'small'=>$this->getImageUrl('image1.png','small'),
                'large'=>$this->getImageUrl('image1.jpg','large'),
                'alt'=>'Alt Image 1',
            ],
        ];        
    }
    /**
     * Get gallery example data
     */
    public function getGalleryExampleData()
    {
        $this->config = [
            'galleryActiveClass'=>'active', 
            'cursor'=>'crosshair', //pointer
            'scrollZoom' => true,
            'zoomWindowFadeIn' => 500,
            'zoomWindowFadeOut' => 750,
            'zoomWindowWidth' => 500, 
            'zoomWindowHeight' => 500, 
            'imageCrossfade'=>true, 
            'borderSize'=>0, 
            'loadingIcon'=>'loading.gif', 
        ];
        $this->images = [
            'image1'=>[
                'active'=>true,
                'thumb'=>$this->getImageUrl('image1.jpg','thumb'),
                'small'=>$this->getImageUrl('image1.png','small'),
                'large'=>$this->getImageUrl('image1.jpg','large'),
                'alt'=>'Alt Image 1',
            ],
            'image2'=>[
                'thumb'=>$this->getImageUrl('image2.jpg','thumb'),
                'small'=>$this->getImageUrl('image2.png','small'),
                'large'=>$this->getImageUrl('image2.jpg','large'),
                'alt'=>'Alt Image 2',
            ],
            'image3'=>[
                'thumb'=>$this->getImageUrl('image3.jpg','thumb'),
                'small'=>$this->getImageUrl('image3.png','small'),
                'large'=>$this->getImageUrl('image3.jpg','large'),
                'alt'=>'Alt Image 3',
            ],
            'image4'=>[
                'thumb'=>$this->getImageUrl('image4.jpg','thumb'),
                'small'=>$this->getImageUrl('image4.png','small'),
                'large'=>$this->getImageUrl('image4.jpg','large'),
                'alt'=>'Alt Image 4',
            ],
        ];
    }

}
