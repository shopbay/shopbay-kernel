<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SGridBlockWidgetTrait
 * All these are draggable palette widgets (serve as widget template to be moved into editor)
 * 
 * @author kwlok
 */
trait SGridBlockWidgetTrait 
{
    protected $widgetViewBasealias = 'common.widgets.sgridlayout.widgets.views';
    public $widgetId;//the widget id (assigned from javascript)
    /**
     * The init function (called during constructor)
     */
    public function init()
    {
        $this->modal = true;//always run in modal mode
    }    
    /**
     * @inheritdoc
     */    
    public function getViewFile()
    {
        return $this->getWidgetViewFile('_column');
    }    
    /**
     * @return Widget type 
     */    
    public function getWidgetType()
    {
        return 'sgrid'.$this->type;
    }    
    /**
     * @return Widget form id 
     */    
    public function getWidgetFormId()
    {
        return $this->widgetType.'_form';
    }    
    /**
     * @return Widget form options 
     */    
    public function getWidgetFormOptions()
    {
        return '';//for child class to implement
    }    
    /**
     * @return Widget form id 
     */    
    public function getWidgetCssClass()
    {
        return $this->widgetType.'-modal';
    }       
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return 'Widget';//for child class to give a proper name
    }    
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_common_widget_settings');//pseudo file; for child class to allocate
    }           
    /**
     * @return Widget css settings
     */    
    public function getWidgetCssSettings()
    {
        return $this->getWidgetViewFile('_common_css_settings');
    }           
    /**
     * @return Widget view file
     */    
    public function getWidgetViewFile($view)
    {
        return $this->widgetViewBasealias.'.'.$view;
    }           
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        $this->content = null;//do nothing, let child class implement
    } 
    
    public function getBlockContent()
    {
        $this->renderBlock();
        return $this->content;
    }    
    
    public function getLanguageForm($field,$htmlOptions=[],$inputType='textField',$fieldType='language',$return=false,$value=null)
    {
        $output = $this->controller->renderPartial($this->getWidgetViewFile('_language_form'),[
                'inputType'=>$inputType,
                'field'=>$field,
                'fieldType'=>$fieldType,
                'element'=> $this,
                'htmlOptions'=>$htmlOptions,
                'value'=>$value,
            ],true);
        
        if ($return)
            return $output;
        else
            echo $output;
    }

}
