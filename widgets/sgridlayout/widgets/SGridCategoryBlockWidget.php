<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridCategoryBlock");
/**
 * Description of SGridCategoryBlockWidget
 * 
 * Display a finite number of items (default to one page data provider size)
 *
 * @author kwlok
 */
class SGridCategoryBlockWidget extends SGridCategoryBlock
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Category Block');
    }         
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_categoryblock_settings');
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if (!isset($this->title)){
            $this->title = $this->siiField('Enter title');//auto translation inside
        }
        
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_categoryblock'),['element'=>$this],true);
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     

}
