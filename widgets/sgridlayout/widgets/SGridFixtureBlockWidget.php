<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridFixtureBlock");
/**
 * Description of SGridFixtureBlockWidget
 *
 * @author kwlok
 */
class SGridFixtureBlockWidget extends SGridFixtureBlock
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Fixture Block');
    }         
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_fixtureblock_settings');
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_fixtureblock'),['element'=>$this],true);
        
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     
}
