<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
/**
 * Description of SGridContainerBlock
 * Support rows within column
 *
 * @author kwlok
 */
class SGridContainerBlock extends SGridColumn
{
    public $type = SGridLayout::CONTAINER_BLOCK;
    /**
     * string the column size (1 - 12)
     */
    public $size = 6;//default width
    /**
     * Container title
     * @var string
     */
    public $title;
    /**
     * The rows inside column
     * @var string 
     */
    public $rows = [];
    /**
     * Include external container
     * @var string 
     */
    public $container;
    /**
     * Render view file
     * @return string
     */
    public function render()
    {
        $this->classPrefix = 'sgrid'.SGridLayout::CONTAINER_BLOCK.' col-md-';
        $this->renderBlock();//render block content first
        return parent::render();
    }   
    /**
     * Render block view
     * @return string
     */
    protected function renderBlock()
    {
        $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._containerblock',['element'=>$this],true);
        if ($this->modal){
            $widget = $this->cloneAsWidget([
                //local settings
                'rows'=>$this->rows,
                'container'=>$this->container,//exterinal container include
            ]);
            $this->content .= $widget->blockContent;//load modal form
        }
    }     
    /**
     * @return array of SGridRow object for rendering
     */
    public function getRowElements()
    {
        $rows = [];
        //transform row to type SGridRow
        foreach ($this->rows as $row) {
            $rows[] = new SGridRow($this->owner, $this->controller, array_merge(['modal'=>$this->modal],$row));
        }          
        return $rows;
    }
}
