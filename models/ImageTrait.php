<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ImageTrait
 *
 * @author kwlok
 */
trait ImageTrait 
{
    /**
     * Create thumbnail of this image.
     * @param string $version the image version to render.
     * @return the url of thumbnail
     */
    public function createThumbnail($version) 
    {        
        if ($version!=Image::VERSION_ORIGINAL){
            if (!Yii::app()->image->existsVersion($this->id,$version)){
                $thumb=Yii::app()->image->createVersion($this->id,$version,get_class($this));
            }
        }

        return Yii::app()->image->getURL($this->id, $version,request()->isSecureConnection,get_class($this));
    }
    /**
     * Renders this image.
     * @param string $version the image version to render.
     * @param string $alt the alternative text.
     * @param array $htmlOptions the html options.
     */
    public function render($version,$alt='',$htmlOptions=array()) 
    {        
        if ($this->isExternalImage)
            return CHtml::image($this->getUrl(),$alt,array_merge($htmlOptions,array('width'=>$version.'px')));
        else
            return CHtml::image($this->createThumbnail($version),$alt,$htmlOptions);
    }    

    public function getIsExternalImage()
    {
        return $this->name == Image::EXTERNAL_IMAGE;
    }
    
}
