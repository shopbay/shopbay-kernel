<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ImageExternal
 * A child class of model Image
 *
 * @author kwlok
 */
class ImageExternal extends Image 
{
    /*
     * An index to separte external image when owner has multiple external images
     */
    public $image_key = 0;   
    /**
     * Init model with default values for external image
     */
    public function init()
    {
        $this->name = Image::EXTERNAL_IMAGE;
        $this->filename = Image::EXTERNAL_IMAGE.'.'.$this->image_key;
        $this->mime_type = 'unset';
        $this->size = -1;
    }     
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            //extra rules to validate external image
            array('src_url', 'url'),
            array('image_key', 'required'),
            array('image_key', 'numerical', 'integerOnly'=>true),
        ));
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'src_url' => Sii::t('sii','Image Url'),
        );
    }    
    /*
     * Set image url
     */
    public function setUrl($url)
    {
        $this->src_url = $url;
    }
    /*
     * Set image key
     */
    public function setImageKey($key)
    {
        $this->image_key = $key;
        $this->filename = Image::EXTERNAL_IMAGE.'.'.$this->image_key;
    }      
}
