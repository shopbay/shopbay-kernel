<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.widgets.SGridBlockWidgetTrait");
Yii::import("common.widgets.sgridlayout.elements.SGridMenuBlock");
Yii::import("common.modules.pages.models.Page");
Yii::import("common.modules.shops.components.ShopPage");
/**
 * Description of SGridMenuBlockWidget
 *
 * @author kwlok
 */
class SGridMenuBlockWidget extends SGridMenuBlock
{
    use SGridBlockWidgetTrait;
    /**
     * @return Widget name 
     */    
    public function getWidgetName()
    {
        return Sii::t('sii','Menu Block');
    }         
    /**
     * @return Widget settings form file
     */    
    public function getWidgetSettings()
    {
        return $this->getWidgetViewFile('_menublock_settings');
    }               
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        if (!isset($this->title)){
            $this->title = $this->siiField('Menu');//auto translation inside
        }
        if (empty($this->menu)){
            $this->menu = [
                [
                    'url' => '#menuitem1',
                    'label' => $this->siiField('Menu item 1'),
                ],
                [
                    'url' => '#menuitem2',
                    'label' => $this->siiField('Menu item 2'),
                ],
                [
                    'url' => '#menuitem3',
                    'label' => $this->siiField('Menu item 3'),
                ],
            ];
        }
        
        $this->content = $this->controller->renderPartial($this->getWidgetViewFile('_menublock'),['element'=>$this],true);
        
        //append config for (modal content)
        $this->content .= $this->controller->renderPartial($this->getWidgetViewFile('_common_modal'),['element'=>$this],true);
    }     
    /**
     * Render menu selection in modal form
     */
    public function renderMenuSelections()
    {
        $output = CHtml::openTag('ol');
        for ($i=0; $i<count($this->menu); $i++) {
            
            $currentMenu = '';//always reset from start
            if (isset($this->menu[$i]['id']))
                $currentMenu = $this->menu[$i]['id'];
            
            $output .= CHtml::openTag('li');
            $output .= CHtml::openTag('select',[
                'class'=>'form-control menu-item-field',
            ]); 
             //insert placeholder as first menu item
            $output .= '<option value="0" data-url="#">'.Sii::t('sii','Select menu item').'</option>';
            
            foreach ($this->getMenuGroupList() as $group => $menuitems) {
                
                $output .= CHtml::openTag('optgroup',['label'=>$this->getMenuGroupLabel($group)]);
        
                foreach ($menuitems as $menuitem) {
                    $selected = $currentMenu==$menuitem['id'] ? 'selected' : '';
                    $output .= '<option '.$selected.' value="'.$menuitem['id'].'" data-type="'.$menuitem['type'].'" data-url="'.$menuitem['url'].'" data-label="'.CHtml::encode(json_encode($menuitem['label'])).'">'.$this->getLanguageValue($menuitem['label'],true).'</option>';
                }
                    
                $output .= CHtml::closeTag('optgroup');
            } 
            $output .= CHtml::closeTag('select'); 
            
            if ($i==0){
                $output .= CHtml::tag('span',['class'=>'select-control add-btn'],'<i class="fa fa-plus-square-o"></i>');            
                $output .= CHtml::tag('span',['class'=>'select-control remove-btn-template','style'=>'display:none'],'<i class="fa fa-minus-square-o"></i>');            
            }
            else
                $output .= CHtml::tag('span',['class'=>'select-control remove-btn'],'<i class="fa fa-minus-square-o"></i>');            
            
            $output .= CHtml::closeTag('li');            
        }
        $output .= CHtml::closeTag('ol');
        return $output;
    }
    
    protected function getMenuGroupList()
    {
        $list = $this->layout->getMenuGroupList();
        return $list;
    }
    
    protected function getMenuGroupLabel($group)
    {
        $label = $this->siiField($group);
        return $this->getLanguageValue($label, true);
    }    

}
