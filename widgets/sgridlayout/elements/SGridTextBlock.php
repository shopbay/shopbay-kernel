<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridTextBlock
 *
 * @author kwlok
 */
class SGridTextBlock extends SGridColumn
{
    public $type = SGridLayout::TEXT_BLOCK;
    /**
     * string the column size (1 - 12)
     */
    public $size = 4;//default width
    /**
     * Text title 
     * @var string
     */
    public $title;
    /**
     * Free text
     * @var array of text
     */
    public $text = [];
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
                'title'=>$this->title,
                'text'=>$this->text,
            ]);
            $this->content = $widget->blockContent;
        }
        else {
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._textblock',['element'=>$this],true);
        }
    }     
    /**
     * Render text
     * @return string
     */
    public function renderText()
    {
        $p = '';
        foreach ($this->text as $text) {
            $p .=  CHtml::tag('p',['class'=>'text-wrapper'],$this->getLanguageValue($text,true));
        }
        return $p;
    }         
}
