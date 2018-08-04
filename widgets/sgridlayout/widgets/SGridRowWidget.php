<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridRow");
/**
 * Description of SGridTextBlockWidget
 *
 * @author kwlok
 */
class SGridRowWidget extends SGridRow
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Row Block');
    }         
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_row_settings');
    }        
    /**
     * @return Widget css settings
     */    
    public function getWidgetCssSettings()
    {
        return $this->getWidgetViewFile('_row_css_settings');
    }     
    /**
     * Render view file
     * @return string
     */
    public function render()
    {
        $this->renderBlock();//render block content first
        return $this->controller->renderPartial($this->getWidgetViewFile('_row'),['element'=>$this],true);
    }       
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        //append config for (modal content)
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     
}
