<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of OwlCarousel
 *
 * @author kwlok
 */
class OwlCarousel extends CWidget
{
    /*
     * @string The id of the widget
     */
    public $id;
    /*
     * Example:
     * array(
     *  //'navigation'=> true, // Show next and prev buttons
     *  'slideSpeed'=> 300,
     *  'paginationSpeed'=> 400,
     *  'singleItem'=>true,    
     *  'autoPlay'=> 5000,//to play every 5 seconds.
     * )
     * @array of config settings for OwlCarousel
     */
    public $config = [];
    /*
     * @array of container html elements 
     */
    public $htmlElements = [];
    /**
     * Init widget
     */
    public function init() 
    {
        parent::init();
        // if not informed will generate Yii defaut generated id, since version 1.6
        if(!isset($this->id))
            $this->id = $this->getId();
        // publish the required assets
        $this->publishAssets();
        //render carousel container
        $this->render('index');
    }
    /**
     * Publish assets
     * @throws Exception
     */
    public function publishAssets()
    {
        $assets = dirname(__FILE__).'/assets';
        $baseUrl = Yii::app()->assetManager->publish($assets);
        if(is_dir($assets)){
            Yii::app()->clientScript->registerCssFile($baseUrl.'/owl.carousel.css');
            Yii::app()->clientScript->registerCssFile($baseUrl.'/owl.theme.css');
            Yii::app()->clientScript->registerScriptFile($baseUrl . '/owl.carousel.min.js', CClientScript::POS_HEAD);
        } else {
            throw new CException('OwlCarousel Error: Could not find assets to publish.');
        }
    }
    /**
     * Run widget
     */
    public function run()
    {
        $config = CJavaScript::encode($this->config);
        Yii::app()->clientScript->registerScript($this->getId(), "$('#$this->id').owlCarousel($config);");
    }
    
    protected function getContainerElements()
    {
        $elements = '';
        foreach ($this->htmlElements as $value) {
            $elements .= CHtml::tag('div', ['class'=>'item'], $value);
        }
        return $elements;
    }
    
}