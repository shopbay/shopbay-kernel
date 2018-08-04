<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridHtmlBlock");
/**
 * Description of SGridHtmlBlockWidget
 *
 * @author kwlok
 */
class SGridHtmlBlockWidget extends SGridHtmlBlock
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Html Block');
    }         
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->widgetViewBasealias.'._htmlblock_settings';
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if (!isset($this->html)){
            $this->html = $this->siiField('Enter your custom content here');//auto translation inside
        }
        elseif (is_scalar($this->html)) {
            $this->html = $this->siiField($this->html);//change to locale format (array form)
        }
        
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_htmlblock'),['element'=>$this],true);
        
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     
}
