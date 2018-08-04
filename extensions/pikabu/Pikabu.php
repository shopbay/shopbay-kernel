<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of Pikabu
 *
 * @author kwlok
 */
class Pikabu extends CWidget
{
    /*
     * @string The id of the widget
     */
    public $id;
    /*
     * @boolean Indicate to use pikabu default theme
     */
    public $useTheme = false;
    /*
     * @array of config settings for Pikabu
     */
    public $config=array();
    /**
     * Init widget
     */
    public function init() 
    {
        parent::init();
        // if not informed will generate Yii defaut generated id, since version 1.6
        if(!isset($this->id))
            $this->id=$this->getId();
        // publish the required assets
        $this->publishAssets();
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
            Yii::app()->clientScript->registerCSSFile($baseUrl.'/pikabu.css');
            if ($this->useTheme)
                Yii::app()->clientScript->registerCSSFile($baseUrl.'/pikabu-theme.css');
            Yii::app()->clientScript->registerScriptFile($baseUrl . '/pikabu.min.js', CClientScript::POS_HEAD);
        } else {
            throw new CException('Pikabu Error: Could not find assets to publish.');
        }
    }
    /**
     * Run widget
     */
    public function run()
    {
        $config = CJavaScript::encode($this->config);
        Yii::app()->clientScript->registerScript($this->getId(), "var pikabu = new Pikabu($config);");
    }
    
}