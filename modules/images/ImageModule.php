<?php
/**
 * Image module class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version 1.0.2
 */
class ImageModule extends CWebModule
{
    /**
     * @property string the relative path where to store images.
     */
    public $imagePath='files/images/';
    /**
     * @property string the name of the web server access file, e.g. ".htaccess" or "ht.acl".
     */
    public $accessFileName='.htaccess';
    /**
     * @property boolean whether to create images on-demand.
     * Disabled by default because it requires Apache module mod_rewrite to be enabled.
     */
    public $createOnDemand=false;
    /**
     * @property boolean whether the installer is enabled.
     */
    public $install=false;

    /**
     * Initializes the module.
     */
    public function init()
    {
        $this->setImport(array(
            'image.components.*',
            'image.vendors.phpthumb.*',
        ));

        if($this->install===true)
        {
            $this->setComponents(array(
                'installer'=>array(
                        'class'=>'ImgInstaller',
                        'module'=>$this,
                )
            ));

            $this->defaultController='install';
        }

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
        $assetsPath=Yii::getPathOfAlias('image.assets');

        // Republish the assets if debug mode is enabled.
        if($this->debug===true)
                return Yii::app()->assetManager->publish($assetsPath,false,-1,true);
        else
                return Yii::app()->assetManager->publish($assetsPath);
    }

    /**
     * Returns the module version.
     * @return string
     */
    public function getVersion()
    {
            return '1.0.2';
    }
}
