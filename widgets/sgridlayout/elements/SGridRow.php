<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.*");
/**
 * Description of SGridRow
 *
 * @author kwlok
 */
class SGridRow extends SGridElement
{
    public $type = SGridLayout::ROW;
    /**
     * The columns configuration for the row
     * @property array
     */
    public $columns = [];   
    /**
     * Support include external row 
     * @property string
     */
    public $include;   
    /**
     * @inheritdoc
     */    
    public function getViewFile()
    {
        return 'common.widgets.sgridlayout.views._row';
    }     
    /**
     * Render view file
     * @return string
     */
    public function render()
    {
        if ($this->modal){
            $widget = $this->cloneAsWidget([
                //local settings
                'columns'=>$this->columns,
                'include'=>$this->include,
            ]);
            $this->content = $widget->blockContent;//load modal form
        }
        
        return parent::render();
    }    
    /**
     * @return array of SGridColumns object for rendering
     */
    public function getColumnElements()
    {
        $columns = [];
        //construct column according to types
        foreach ($this->columns as $col) {
            if (isset($col['type']) && SGridLayout::existsType($col['type'])){
                //a specific element type is declared
                $element = 'SGrid'.ucfirst(str_replace('block', 'Block', $col['type']));//including upper case 'Block'
                $columns[] = $this->createElement($element, $col);
            }
            else {
                //default element
                $columns[] = $this->createElement('SGridHtmlBlock', $col);
            }
        }          
        return $columns;
    }
    
    protected function createElement($type,$config) 
    {
        return new $type($this->owner, $this->controller,array_merge(['modal'=>$this->modal],$config));//pass down its modal mode setting
    }
}
