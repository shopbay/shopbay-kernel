<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridSlideBlock");
/**
 * Description of SGridSlideBlockWidget
 *
 * @author kwlok
 */
class SGridSlideBlockWidget extends SGridSlideBlock
{
    use SGridBlockWidgetTrait;
    
    private $_hasImages = false;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Slide Block');
    }         
    /**
     * Add below to support file upload
     * @return Widget form options 
     */    
    public function getWidgetFormOptions()
    {
        return 'enctype="multipart/form-data" method="post" action="'.$this->controller->getUploadUrl(true).'"';
    }    
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_slideblock_settings');
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if (empty($this->items)){
            $this->items = [
                [
                    'content' => $this->getDefaultImage(),
                    'caption' => '<p>'.Sii::t('sii','Image size should be minimum 1024 x 480 to get best quality presentation.').'</p>',
                ],
                [
                    'content' => $this->getDefaultImage(),
                    'caption' => '<p>'.Sii::t('sii','Each slide show image should have same width and height to get best quality presentation.').'</p>',
                ]
            ];
        }
        else
            $this->_hasImages = true;//set has images flag

        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_slideblock'),['element'=>$this],true);
        
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     
    /**
     * @see This method is required by SImageManager
     * @return string
     */
    public function getDefaultImage()
    {
        return '<i class="fa fa-photo"></i>';
    }    
    
    public function getHasImages()
    {
        return $this->_hasImages;
    }
    
    public function loadImageRow()
    {
        $html = '';
        foreach ($this->items as $index => $item) {
            $html .= $this->renderImageRow($item, $index);
        }
        return $html;
    }

    public function loadImageRowTemplate()
    {
        //dummy data
        $item = [
            'image'=>'',
            'text'=>[],
            'ctaLabel'=>[],
            'ctaUrl'=>'',
        ];
        return $this->renderImageRow($item, 't','template');
    }
    
    public function renderImageRow($item,$nextNum,$cssClass=null)
    {
        return $this->controller->renderPartial($this->getWidgetViewFile('_slideblock_image_settings'),['element'=>$this,'item'=>$item,'nextNum'=>$nextNum,'cssClass'=>$cssClass],true);
    }    
    
    public function getSlideItemValue($item,$field)
    {
        if (isset($item[$field]) && !empty($item[$field]))
            return $item[$field];
        else 
            return [];//empty value in array format; since this is langauge field
    }
}

