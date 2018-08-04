<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridBoxBlock");
/**
 * Description of SGridBoxBlockWidget
 *
 * @author kwlok
 */
class SGridBoxBlockWidget extends SGridBoxBlock
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Box Block');
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
        return $this->getWidgetViewFile('_boxblock_settings');
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if (!isset($this->caption)){
            $this->caption = $this->siiField('Enter caption');//auto translation inside
        }
        if (!isset($this->link)){
            $this->link = Sii::t('sii','Enter link, e.g. https://yourshop/yourpage');
        }        
        
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_boxblock'),['element'=>$this],true);
        
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     

    public function getDefaultImage()
    {
        return '<i class="fa fa-photo"></i>';
    }
   
}
