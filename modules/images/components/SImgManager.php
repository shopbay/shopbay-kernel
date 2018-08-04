<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
require_once(dirname(__FILE__).'/../vendors/phpthumb/ThumbLib.inc.php'); // Yii::import() will not work in this case.
/**
 * Customized Image manager class file.
 * Customize from ImgManager to suit model Image (s_image) or MediaAssociation (s_media_association)
 * 
 * Provides easy image manipulation with the help of the excellent PHP Thumbnailer library.
 * @see http://phpthumb.gxdlabs.com/
 * 
 * @author kwlok
 */
class SImgManager extends CApplicationComponent
{
    /**
     * @property string The model class to load image; Default to "Image"
     */
    public $modelClass = 'Image';    
    /**
     * @property boolean Whether to use https for image url. Default to "false"
     */
    public $useHttps = false;    
    /**
     * @property string the base url.
     */
    public $baseUrl = 'localhost';    
    /**
     * @property string the base relative path where physical images are stored.
     * It is a relative path
     */
    public $baseRelativePath = 'www';    
    /**
     * PhpThumb options that are passed to the ThumbFactory.
     * Default values are the following:
     *
     * <code>
     * array(
     *     'resizeUp' => false,
     *     'jpegQuality' => 100,
     *     'correctPermissions' => false,
     *     'preserveAlpha' => true,
     *     'alphaMaskColor'	=> array(255, 255, 255),
     *     'preserveTransparency' => true,
     *     'transparencyMaskColor' => array(0, 0, 0),
     * );
     * </code>
     *
     * @property array
     */
    public $thumbOptions=[];
    /**
     * @property string the relative path where to store images.
     */
    public $imagePath='/files/images/';
    /**
     * @property array the image versions.
     */
    public $versions=[];
    /**
     * @property string the base path.
     */
    private $_basePath;
    /**
     * @property string the image version path.
     */
    private $_versionBasePath;

    private static $_thumbOptions=[]; // needed for the static factory-method
    private static $_imagePath;
    /**
     * Initializes the component.
     */
    public function init()
    {
        if (!in_array($this->modelClass,['Image','MediaAssociation']))
            throw new ImgException(Img::t('error','Unsupported image model: '.$this->modelClass));
                
        $this->baseUrl = Yii::app()->urlManager->createCdnUrl(null,$this->useHttps||request()->isSecureConnection?'https':'http');
        self::$_thumbOptions=$this->thumbOptions;
        self::$_imagePath=$this->getImagePath(true);
    }

    public function getBaseUrl($forceSecure=false)
    {
        if (request()->isSecureConnection||$forceSecure)
            return Yii::app()->urlManager->createCdnUrl(null,'https');
        else
            return $this->baseUrl;
    }
    /**
     * Returns the URL for a specific image.
     * @param string $id the image id.
     * @param string $version the name of the image version.
     * @return string the URL.
     * @throws CException if the version is not defined.
     */
    public function getURL($id,$version,$forceSecure=false,$modelClass=null)
    {
        $image = $this->loadModel($id,$modelClass);
        if ($image==null){   
           $image = $this->getDefaultImageModel();
        }
        
        if ($version==Image::VERSION_ORIGINAL){
            if ($image instanceof Image){
                return $this->getBaseUrl($forceSecure).$image->src_url;
            }
            elseif ($image instanceof MediaAssociation){
                return $image->getUrl();
            }
            else {
                logError(__METHOD__.' Failed to get URL! Image could not be found.');
                return $this->getDefaultImageUrl();
            }                
        }

        if(isset($this->versions[$version]))
        {
            $options=ImgOptions::create($this->versions[$version]);
            $filename=$this->resolveFileName($image);
            $path=$this->getVersionPath($version);
            return $this->getBaseUrl($forceSecure).$path.$filename;
        }
        else
            throw new ImgException(Img::t('error','Failed to get image URL! Version is unknown.'));
    }
    /**
     * Deletes a specific image.
     * @param $id the image id.
     * @return boolean whether the image was deleted.
     * @throws ImgException if the image cannot be deleted.
     */
    public function delete($id)
    {
        $modelClass = $this->modelClass;
        
        $image = $modelClass::model()->findByPk($id);

        if ($image instanceof Image){
            $filename=$this->resolveFileName($image);
            $filepath=$this->getImagePath(true).$filename;

            if($image->delete()===false)
                throw new ImgException(Img::t('error', 'Failed to delete image! Record could not be deleted.'));

            if(file_exists($filepath)!==false && unlink($filepath)===false)
                throw new ImgException(Img::t('error', 'Failed to delete image! File could not be deleted.'));

            foreach($this->versions as $version=>$config)
                $this->deleteVersion($image, $version);
        }
        else
            throw new ImgException(Img::t('error', 'Failed to delete image! Record could not be found.'));
    }

    /**
     * Deletes a specific image version.
     * @param Image $image the image model.
     * @param string $version the image version.
     * @return boolean whether the image was deleted.
     * @throws ImgException if the image cannot be deleted.
     */
    private function deleteVersion($image,$version)
    {
        if(isset($this->versions[$version])) {
            $filepath=$this->resolveImageVersionPath($image,$version);

            if(file_exists($filepath)!==false && unlink($filepath)===false)
                throw new ImgException(Img::t('error', 'Failed to delete the image version! File could not be deleted.'));
        }
        else
            throw new ImgException(Img::t('error', 'Failed to delete image version! Version is unknown.'));
    }
    /**
     * Loads a thumb of a specific image.
     * @param integer $id the image id.
     * @return ThumbBase
     */
    public function loadThumb($id)
    {
        $image=$this->loadModel($id);

        if($image!==null)
        {
            $fileName=$this->resolveFileName($image);
            $thumb=self::thumbFactory($fileName);
            return $thumb;
        }
        else
            return null;
    }
    /**
     * Loads a specific image model.
     * @param integer $id the image id.
     * @param string the $modelClass
     * @return Image
     */
    public function loadModel($id,$modelClass=null)
    {
        if (!isset($modelClass))
            $modelClass = $this->modelClass;
        return $modelClass::model()->findByPk($id);
    }
    /**
     * Check if a image version exists
     * @param type $id
     * @param type $version
     */
    public function existsVersion($id,$version)
    {
        if(isset($this->versions[$version])){
            $image = $this->loadModel($id);
            if($image!=null){
                $fileName=$this->resolveFileName($image);
                $path=$this->getVersionPath($version,true);
                return file_exists($path.$fileName);
            } 			
        }
        else
            throw new ImgException(Img::t('error','Failed to check version existence! Version is unknown.'));
    }
    /**
     * Creates a new version of a specific image.
     * @param integer $id the image id.
     * @param string $version the image version.
     * @param string $modelClass the model class to create version.
     * @return ThumbBase
     */
    public function createVersion($id,$version,$modelClass=null)
    {
        if(isset($this->versions[$version])){
            
            $image = $this->loadModel($id,$modelClass);
            if ($image==null){
                $image = $this->getDefaultImageModel();
            }

            if($image!=null){
                $fileName=$this->resolveFileName($image);
                if ($image instanceof Image){
                    $thumb=self::thumbFactory($this->getBasePath().$image->src_url);
                }
                elseif ($image instanceof MediaAssociation){
                    $thumb=self::thumbFactory($image->filepath);
                }
                $options=ImgOptions::create($this->versions[$version]);
                $thumb->applyOptions($options);
                $path=$this->getVersionPath($version,true);
                //logTrace(__METHOD__.' thumbnail creation: $fileName='.$fileName.', $version='.$version.', $path='.$path,get_class($image));
                return $thumb->save($path.$fileName);
            }
            else {
                throw new ImgException(Img::t('error','Failed to create version! Image could not be found.'));
            }                
        }
        else
            throw new ImgException(Img::t('error','Failed to create version! Version is unknown.'));
    }
    /**
     * Returns the original image file name.
     * @param Image $image the image model.
     * @return string the file name.
     */
    private function resolveFileName($image)
    {
        return $image->filename;
    }
    /**
     * Returns the path to a specific image version.
     * @param Image $image the image model.
     * @param string $version the image version.
     * @return string the path.
     */
    private function resolveImageVersionPath($image,$version)
    {
        $filename=$this->resolveFileName($image);
        return get_class($image)==$this->modelClass? $this->getVersionPath($version,true).$filename : null;
    }
    /**
     * Returns the base path.
     * @return string the path.
     */
    public function getBasePath()
    {
        if($this->_basePath!==null)
            return $this->_basePath;
        else
            return $this->_basePath= dirname(__FILE__).'/../../../../'.$this->baseRelativePath;
    }
    /**
     * Returns the images path.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function getImagePath($absolute=false)
    {
        $path='';

        if($absolute===true)
            $path.=$this->getBasePath();

        return $path.$this->imagePath;
    }
    /**
     * Returns the image version path.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    private function getVersionBasePath($absolute=false)
    {
        $path='';

        if($absolute===true)
            $path.=$this->getBasePath();

        if($this->_versionBasePath!==null)
            $path.=$this->_versionBasePath;
        else{
            $path.=$this->_versionBasePath = $this->getImagePath().'versions/';
            if ($absolute) // Might be a new version so we need to create the path if it doesn't exist.
                if(!file_exists($path))
                    mkdir($path);
        }

        return $path;
    }
    /**
     * Returns the version specific path.
     * @param string $version the name of the image version.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    private function getVersionPath($version,$absolute=false)
    {
        $path=$this->getVersionBasePath($absolute).$version.'/';
        $abspath = $path;
        // Might be a new version so we need to create the path if it doesn't exist.
        if (!$absolute) 
            $abspath = $this->getVersionBasePath(true).$version.'/';
        
        if(!file_exists($abspath))
            mkdir($abspath);
        return $path;
    }
    /**
     * Creates a new image.
     * @param string $fileName the file name.
     * @return ImgThumb
     */
    private static function thumbFactory($fileName)
    {
        //$phpThumb=PhpThumbFactory::create(self::$_imagePath.$fileName,self::$_thumbOptions);
        $phpThumb=PhpThumbFactory::create($fileName,self::$_thumbOptions);
        return new ImgThumb($phpThumb);
    }

    public function getDefaultImageUrl()
    {
        return $this->getDefaultImageModel()->getUrl();
    }        
    
    public function getDefaultImageModel()
    {
        $original = $this->modelClass;
        $this->modelClass = 'Image';
        $model = $this->loadModel(Image::DEFAULT_IMAGE);
        $this->modelClass = $original;//restore back
        return $model;
    }      
}