<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridImageBlock");
/**
 * Description of SGridImageBlockWidget
 *
 * @author kwlok
 */
class SGridImageBlockWidget extends SGridImageBlock
{
    use SGridBlockWidgetTrait;
    /**
     * Call to action label
     * @var string
     */
    public $ctaLabel; 
    /**
     * Call to action url
     * @var string
     */
    public $ctaUrl; 
    /**
     * The background image url
     * @var string
     */
    public $bgImage;//to avoid using 'image' name in case of name clashing
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Image Block');
    }         
    /**
     * Add below to support file upload
     * @return Widget form options 
     */    
    public function getWidgetFormOptions()
    {
        return 'enctype="multipart/form-data" method="post" action="'.$this->controller->uploadUrl.'"';
    }    
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_imageblock_settings');
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if (!isset($this->title)){
            $this->title = $this->siiField('Enter heading');//auto translation inside
        }
        if (!isset($this->desc))
            $this->desc = $this->siiField('Enter description');
        if (!isset($this->ctaLabel)){
            $this->ctaLabel = $this->siiField('Learn More');
        }        
        if (!isset($this->ctaUrl)){
            $this->ctaUrl = Sii::t('sii','Enter button link, e.g. https://yourshop/yourpage');
        }        
        
        //always set background image as style
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_imageblock'),['element'=>$this],true);
        
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     

    public function getDefaultImage()
    {
        return '<i class="fa fa-photo"></i>';
    }
    /**
     * Decide if to inclue bgImage in style
     */
    public function getStyleWithBgImage()
    {
        if (isset($this->bgImage))
            return $this->style.';background-image:url('.$this->bgImage.');';//always append
        else
            return $this->style;
    }
           
}
