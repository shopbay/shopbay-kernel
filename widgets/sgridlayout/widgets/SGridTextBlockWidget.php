<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridTextBlock");
/**
 * Description of SGridTextBlockWidget
 *
 * @author kwlok
 */
class SGridTextBlockWidget extends SGridTextBlock
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Text Block');
    }         
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_textblock_settings');
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
        if (empty($this->text)){
            $this->text = [
                $this->siiField('Enter text'),//auto translation inside
                $this->siiField('Enter text'),
            ];
        }
        
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_textblock'),['element'=>$this],true);
        
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     
    /**
     * Render text paragraph in modal form
     */
    public function renderTextParagraph()
    {
        $output = '';            
        foreach ($this->text as $index => $text) {
            
            $output .= CHtml::openTag('div',['class'=>'paragraph-wrapper']);
            
            if ($index==0){
                $output .= CHtml::tag('span',['class'=>'select-control add-btn'],'<i class="fa fa-plus-square-o"></i>');            
                $output .= CHtml::tag('span',['class'=>'select-control remove-btn-template','style'=>'display:none'],'<i class="fa fa-minus-square-o"></i>');            
            }
            else
                $output .= CHtml::tag('span',['class'=>'select-control remove-btn'],'<i class="fa fa-minus-square-o"></i>');            
            
            $output .= CHtml::openTag('div',['class'=>'textarea-wrapper']);
            $output .= $this->getLanguageForm('text_'.$index,[
                        'class'=>'form-control language-field-text',
                        'data-field-type'=>'text',
                        'rows'=>3,
                        'placeholder'=>Sii::t('sii','Enter text'),
                    ],'textArea','language',true,$text);
            $output .= CHtml::closeTag('div');
            $output .= CHtml::closeTag('div');
        }
        return $output;
    }    
}
