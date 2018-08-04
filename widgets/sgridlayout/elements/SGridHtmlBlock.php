<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridHtmlBlock
 * The default column block when type is not specified
 * It accepts any free form html 
 * @author kwlok
 */
class SGridHtmlBlock extends SGridColumn
{
    public $type = SGridLayout::HTML_BLOCK;
    /**
     * Any html 
     * @var string
     */
    public $html;
    /**
     * Render view file
     * @return string
     */
    public function render()
    {
        $this->renderBlock();//render block content first
        return parent::render();
    }   
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if ($this->modal){
            $widget = $this->cloneAsWidget([
                //local settings
                'html'=>$this->html,
            ]);
            $this->content = $widget->blockContent;
        }
        else {
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._htmlblock',['element'=>$this],true);
        }
    }     
}
