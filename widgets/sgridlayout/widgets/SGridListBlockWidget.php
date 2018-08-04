<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridListBlock");
/**
 * Description of SGridListBlockWidget
 * 
 * Display a finite number of items (linked to a fixed data provider)
 *
 * @author kwlok
 */
class SGridListBlockWidget extends SGridListBlock
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','List Block');
    }         
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_listblock_settings');
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {        
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_listblock'),['element'=>$this],true);
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     
    
    public function renderBeforeListView()
    {
        if (isset($this->beforeViewFile)){
            return parent::renderBeforeListView();
        }
        elseif (strlen($this->getLanguageValue('title'))==0){
            //Need to have a empty placeholder in case when user enter title again inside editor
            return Sii::t('sii','<h2 id="title" class="form-field" data-field="title" data-field-type="text"></h2>');
        }
        elseif (strlen($this->getLanguageValue('title'))>0){
            return Sii::t('sii','<h2 id="title" class="form-field" data-field="title" data-field-type="text">{title}</h2> <span class="total">{count}</span>',[$this->getDataProvider()->getTotalItemCount(),'{title}'=>$this->getLanguageValue('title')]);
        }
        else
            return '';
    }    

}
