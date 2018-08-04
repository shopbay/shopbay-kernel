<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridBoxBlock
 *
 * @author kwlok
 */
class SGridBoxBlock extends SGridColumn
{
    public $type = SGridLayout::BOX_BLOCK;
    /**
     * string the column size (1 - 12)
     */
    public $size = 4;//default width
    /**
     * Box item caption
     * @var string
     */
    public $caption;
    /**
     * Box image url
     * @var string
     */
    public $boxImage;
    /**
     * Box item link
     * @var string
     */
    public $link; 
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
                'caption'=>$this->caption,
                'link'=>$this->link,
                'boxImage'=>$this->boxImage,
            ]);
            $this->content = $widget->blockContent;
        }
        else {        
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._boxblock',['element'=>$this],true);
        }
    }       
}
