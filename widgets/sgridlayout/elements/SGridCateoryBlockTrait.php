<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SGridCateoryBlockTrait
 *
 * @author kwlok
 */
trait SGridCateoryBlockTrait 
{
    /**
     * Text title 
     * @var string
     */
    public $title;
    /**
     * List items
     * @var CDataProvider
     */
    public $dataProvider;
    /**
     * Single item view
     * @var string
     */
    public $itemView;
    /**
     * Item shown per row
     * If $itemsLimit equals to this, then only one row will be displayed
     * @var integer [1-5]
     */
    public $itemsPerRow = 4;   
    /**
     * Item total to be displayed per row
     * If $itemsPerRow equals to this, then only one row will be displayed
     * @var integer 
     */
    public $itemsLimit = 4;   
    /**
     * Item script (to execute on each item)
     * @var string
     */
    public $itemScript;
    /**
     * Data to pass into $itemView
     * @var array
     */
    public $viewData = [];
    /**
     * Auto compute adaptive width
     * @return type
     */
    protected function getItemWidth()
    {
        if ($this->itemsPerRow > 5 || $this->itemsPerRow < 1)
            $this->itemsPerRow = 4;//set to default, range only between 1 to 5
        
        return round((1 / $this->itemsPerRow) * 100 ,2) - 0.5;//0.5% is for offset use
    }
    /**
     * Make a css compatible name
     * @return type
     */
    protected function getItemCssClass()
    {
        return 'p'.str_replace('.', '-', $this->itemWidth);
    }
        
    public function renderScript()
    {
        if (isset($this->itemScript))
            cs()->registerScript(__CLASS__.rand(1, 1000),$this->itemScript,CClientScript::POS_END);
    }
}
