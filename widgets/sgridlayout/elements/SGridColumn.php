<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridElement");
/**
 * Description of SGridColumn
 * Refer to http://getbootstrap.com/css/#grid
 * 
 * @author kwlok
 */
class SGridColumn extends SGridElement
{
    public $type = SGridLayout::COLUMN;
    /**
     * Boostrap class prefix: col-xs-, col-sm-, col-md-, col-lg-
     * @var type 
     */
    public $classPrefix	= 'col-md-';
    /**
     * Get css class
     */
    public function getCssClass()
    {
        return $this->classPrefix.$this->size;
    }
    /**
     * @inheritdoc
     */    
    public function getViewFile()
    {
        return 'common.widgets.sgridlayout.views._column';
    } 

}
