<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.images.ImageModule");
/**
 * Customize "components.ImgManager / private function getBasePath()" to suit for deployment directory structure
 * Extended from ImageModule
 * 
 * @author kwlok
 */
class ImagesModule extends ImageModule
{
    /**
     * @property string the relative path where to store images.
     */
    public $imagePath='files/images/';
    /**
     * Initializes the module.
     */
    public function init()
    {
        $this->setImport([
            'images.components.*',
            'images.vendors.phpthumb.*',
        ]);

        $this->registerScripts();
    }

    /**
     * Registers the necessary CSS files.
     */
    private function registerScripts()
    {
        $assetsURL=$this->getAssetsURL();
        Yii::app()->clientScript->registerCssFile($assetsURL.'/styles.css');
    }

    /**
    * Publishes the module assets path.
    * @return string the base URL that contains all published asset files.
    */
    private function getAssetsURL()
    {
        $assetsPath=Yii::getPathOfAlias('images.assets');

        // Republish the assets if debug mode is enabled.
        //if(YII_DEBUG)
        //    return Yii::app()->assetManager->publish($assetsPath,false,-1,true);
        //else
            return Yii::app()->assetManager->publish($assetsPath);
    }

}