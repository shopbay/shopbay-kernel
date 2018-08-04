<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagelayout.SPageLayout');
/**
 * Description of SDetailView
 * Extended CDetailView to support data columns, image-column, key-value column, content-column
 * 
 * @author kwlok
 */
class SDetailView extends CWidget
{
    public $id;
    public $columns;
    public $column_content = 'content-column';
    public $column_image = 'image-column';
    /**
     * Image column data 
     * @var type 
     */
    public $imageColumnVisible = false; 
    public $imageColumnWidth = '0.35';//multiply by hundred is % 
    public $imageColumnCssClass;//default to null
    /**
     * Key values data delement
     * @var type 
     */
    public $key_value_element = 'key-value-element';
    /**
     * @see CDetailView->$data
     */
    public $data;
    /**
     * @see CDetailView->$attributes
     */
    public $attributes;
    /**
     * @see CDetailView->$attributes
     */
    public $tagName='div';
    /**
     * @see CDetailView->$itemTemplate
     */
    public $itemTemplate='<div class="{class}"><span class="key">{label}</span><span class="value">{value}</span></div>';
    /**
     * @see CDetailView->$itemCssClass
     */
    public $itemCssClass=array('data-element');
    /**
     * @var array the HTML options used for {@link tagName}
     */
    public $htmlOptions=array('class'=>'detail-view rounded');
    /**
     * @see CDetailView->$cssFile
     */
    public $cssFile = false; 
    
    public function init()
    {
        parent::init();
        if (!isset($this->id))
            $this->id = $this->getId();
    }
    /**
     * Renders the detail view.
     * This is the main entry of the whole detail view rendering.
     */
    public function run()
    {
        if (isset($this->columns)&&isset($this->attributes))
            throw new CException(Sii::t('sii','SDetailView attributes have to be defined within columns'));

        if (isset($this->columns)){
            
            $output = CHtml::openTag($this->tagName, array_merge(array('id'=>$this->id),$this->htmlOptions));
            foreach ($this->columns as $attributes) {
                //Image column
                if (isset($attributes[$this->column_image]['image'])){
                    if ($this->_isImageColumnVisible()) {
                        if (isset($attributes[$this->column_image]['width'])){
                            $this->imageColumnWidth = $attributes[$this->column_image]['width'];
                            $output .= CHtml::tag($this->tagName, array('class'=>'data-column image','style'=>'width:'.$this->getColumnWidth(true).'%'),$attributes[$this->column_image]['image']);
                        }
                        if (isset($attributes[$this->column_image]['cssClass'])){
                            $this->imageColumnCssClass = $attributes[$this->column_image]['cssClass'];
                            $output .= CHtml::tag($this->tagName, array('class'=>'data-column image '.$this->getColumnCssClass(true)),$attributes[$this->column_image]['image']);
                        }
                    }
                }
                //Content column
                else if (isset($attributes[$this->column_content])){
                    $output .= CHtml::tag($this->tagName, array('class'=>'data-column content','style'=>'width:'.$this->getColumnWidth().'%'),$attributes[$this->column_content]);
                }
                //Normal detail column
                else {
                    if (isset($attributes[$this->key_value_element]))
                        $attributes = $this->_parseKeyValue($attributes);
                    
                    if ($this->getColumnCssClass()!=false)
                        $output .= $this->renderCDetailView($attributes, array('class'=>'data-column normal '.$this->getColumnCssClass()));
                    else
                        $output .= $this->renderCDetailView($attributes, array('class'=>'data-column','style'=>'width:'.$this->getColumnWidth().'%'));
                }
            }
            $output .= CHtml::closeTag($this->tagName);
            echo $output;
        }
        
        if (isset($this->attributes)){
            echo $this->renderCDetailView($this->attributes,$this->htmlOptions,$this->id);
        }
    }
    
    public function renderCDetailView($attributes,$htmlOptions,$id=null)
    {
        return $this->widget('zii.widgets.CDetailView', array(
                'id'=>$id,
                'data'=>$this->data,
                'cssFile'=>$this->cssFile,
                'tagName'=>$this->tagName,
                'itemTemplate'=>$this->itemTemplate,
                'itemCssClass'=>$this->itemCssClass,
                'attributes'=>$attributes,
                'htmlOptions'=>$htmlOptions,
            ),true);
    }
    
    protected function getColumnCssClass($image=false)
    {
        if ($image)
            return $this->imageColumnCssClass;
        else {
            if ($this->imageColumnVisible){
                if (count($this->columns)>=3){//3 columns and above use different computation
                    $contentColumnCnt = count($this->columns) - 1;/* 1 = image column */
                    return SPageLayout::getCenterHalfWidthPercent($contentColumnCnt);
                }
                else
                    return SPageLayout::getCenterWidthPercent($this->imageColumnCssClass);
            }
            else {
                if (count($this->columns)>1)
                    return SPageLayout::getWidthDividePercent(count($this->columns));
                else
                    return false;
            }
        }
    }    
    protected function getColumnWidth($image=false)
    {
        if ($image)
            return 100*$this->imageColumnWidth;
        else {
            if ($this->imageColumnVisible){
                return 100 * (1-$this->imageColumnWidth) / (count($this->columns) - 1);/* 1 = image column */
            }
            else {
                if ($this->_hasImageColumn()){
                    return 100 / (count($this->columns) - 1);
                }
                else {
                    return 100 / count($this->columns);
                }
            }
        }
    }
    
    private function _parseKeyValue($attributes)
    {
        if (!isset($attributes[$this->key_value_element]))
            throw new CException(Sii::t('sii','SDetailView key value element not found'));
        
        $position;
        foreach (array_keys($attributes) as $key => $value) {
            if ($value==$this->key_value_element)
                $position = (int)$key;
        }
        $keyvalue = new CList();
        foreach ($attributes[$this->key_value_element] as $key => $value) {
            $keyvalue->add(array('name'=>$key,'type'=>'raw','value'=>$value));
        }
        $part1 = array_slice($attributes, 0, $position);
        $part2 = $keyvalue->toArray();
        $part3 = array_slice($attributes, $position+1);//slice $part3 if any
        $attributes = array_merge($part1,$part2,$part3);
        //logTrace(__METHOD__.' after merge',$attributes);
        return $attributes;
    }
    
    private function _hasImageColumn()
    {
        //check if exists image-column
        foreach ($this->columns as $column) {
            if (isset($column[$this->column_image])){
                $imagecolumn = $column[$this->column_image];
                break;
            }
        }
        return isset($imagecolumn)?$imagecolumn:false;
    }
    private function _isImageColumnVisible()
    {
        if (($imagecolumn = $this->_hasImageColumn())!=false){
            //check image-column configuration
            if ((isset($imagecolumn['visible'])&&$imagecolumn['visible']) ||
               (!isset($imagecolumn['visible']))) {
                    $this->imageColumnVisible = true;
            }
        }
        return $this->imageColumnVisible;
    }
    
}
