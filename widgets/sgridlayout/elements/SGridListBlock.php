<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
Yii::import("common.widgets.sgridlayout.elements.SGridCateoryBlockTrait");
/**
 * Description of SGridListBlock
 * 
 * Use {@link SListView} as its underlying list view generator
 *
 * @author kwlok
 */
class SGridListBlock extends SGridColumn
{
    use SGridCateoryBlockTrait;
    
    public $type = SGridLayout::LIST_BLOCK;
    /**
     * The list item (is bind to a specific data provider)
     * @var string
     */
    public $listItem;   
    /**
     * Item css selector
     * @var string
     */
    public $itemSelector = '.item';    
    /**
     * Items list view element id
     * @var string
     */
    public $listViewId = 'catalog';
    /**
     * If to use infinite scroll pager
     * @var boolean
     */
    public $enableInfiniteScroll;
    /**
     * List summary tag name
     * @var string
     */
    public $summaryTagName = 'div';    
    /**
     * Area to render before list view (default used for list summary)
     * param $dataProvider, $page will be passed in
     * @var string
     */
    public $beforeViewFile;    
    /**
     * Area to render after list view
     * param $page will be passed in
     * @var string
     */
    public $afterViewFile;    
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
                //editable settings
                //Settings not covered here are defined at global level, @see layout_map.json
                'title'=>$this->title,
                'listItem'=>$this->listItem,
                'itemsPerRow'=>$this->itemsPerRow,
                'itemsLimit'=>$this->itemsLimit,
                'viewData'=>$this->viewData,
                'beforeViewFile'=>$this->beforeViewFile,
                'afterViewFile'=>$this->afterViewFile,
                'itemScript'=>$this->itemScript,
            ]);
            $this->content = $widget->blockContent;
        }
        else {        
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._listblock',['element'=>$this],true);
        }
    }     
    
    protected function getInfiniteScroll()
    {
        if (!$this->enableInfiniteScroll)
            return [];
        
        $config = [
            'rowSelector'=>$this->itemSelector, 
            'listViewId'=>$this->getListViewId(), 
        ];
        if (isset($this->itemScript))
            $config = array_merge($config,['onRenderComplete'=>$this->itemScript]);
        return $config;
    }
    
    public function getListViewId()
    {
        return isset($this->listViewId) ? $this->listViewId : $this->name;
    }    
    
    public function getListTemplate()
    {
        $template = $this->enableInfiniteScroll ? '{items}{pager}' : '{items}';
        if (is_array($this->title) && !empty($this->title)){
            $template = '{summary}'.$template;
        }
        elseif (strlen($this->title)>0||strlen($this->beforeViewFile)>0){
            $template = '{summary}'.$template;
        }
        return $template;
    }
    /**
     * Render list view based on data provider
     * @return type
     */
    public function renderListView()
    {
        //this view covers before and actual list view
        $view =  $this->controller->widget('common.widgets.SListView', [
            'id' => $this->getListViewId(),
            'summaryTagName'=>$this->summaryTagName,
            'summaryText'=>$this->renderBeforeListView(),
            'dataProvider' => $this->getDataProvider(),
            'htmlOptions' => ['class'=>'catalog-container','data-total'=>$this->getDataProvider()->getTotalItemCount()],
            'itemView' => $this->itemView,
            'itemsCssClass'=>'items '.$this->itemCssClass,
            'viewData' => $this->viewData,
            'template' => $this->getListTemplate(),
            'infiniteScroll'=>$this->infiniteScroll,
        ],true);
        //this view covers after list view
        $after = $this->renderAfterListView();
        //return combine output
        return $view.$after;
    }  
    /**
     * Render list view based on data provider
     * @return type
     */
    public function renderBeforeListView()
    {
        if (strlen($this->beforeViewFile)>0){
            return $this->layout->renderTheme($this->beforeViewFile,[
                'dataProvider'=>$this->getDataProvider(),
                'page'=>$this->layout->page,
            ]);
        }
        elseif (strlen($this->getLanguageValue('title'))>0){
            return Sii::t('sii','<h2>{title}</h2> <span class="total">{count}</span>',[$this->getDataProvider()->getTotalItemCount(),'{title}'=>$this->getLanguageValue('title')]);
        }
        else
            return '';
    }   
    /**
     * Render list view based on data provider
     * @return type
     */
    public function renderAfterListView()
    {
        if (strlen($this->afterViewFile)>0)
            return $this->layout->renderTheme($this->afterViewFile,['page'=>$this->layout->page]);
    }      
    /**
     * Set data provider page size to follow items limit
     * @return type
     */
    public function getDataProvider()
    {
        if (!isset($this->listItem)){
            $rawData = [
                ['icon'=>'<i class="fa fa-photo"></i>'],
                ['icon'=>'<i class="fa fa-photo"></i>'],
                ['icon'=>'<i class="fa fa-photo"></i>'],
                ['icon'=>'<i class="fa fa-photo"></i>'],
            ];
            //default to 4 items, single row
            $this->dataProvider = new CArrayDataProvider($rawData,['keyField'=>false,'sort'=>false]);
            $this->itemView = 'common.widgets.sgridlayout.views._listblock_item';//default item view
        }
        else {
            $page = $this->layout->getPageByListItem($this->listItem);
            $layoutMap = $this->layout->theme->getParam('layout_map');
            //logTrace(__METHOD__,$layoutMap[$page->id]);
            $this->itemView = isset($layoutMap[$page->id]['item_view']) ? $this->layout->getThemeView($layoutMap[$page->id]['item_view']) : 'common.widgets.sgridlayout.views._listblock_item';//default item view
            $this->itemSelector = isset($layoutMap[$page->id]['item_selector']) ? $layoutMap[$page->id]['item_selector'] : $this->itemSelector;//use default
            $this->listViewId = isset($layoutMap[$page->id]['list_view_id']) ? $layoutMap[$page->id]['list_view_id'] : $this->listViewId;//use default
            if (!isset($this->enableInfiniteScroll))
                $this->enableInfiniteScroll = isset($layoutMap[$page->id]['infinite_pagination']) ? $layoutMap[$page->id]['infinite_pagination'] : false;//default to false
            if (empty($this->viewData)){//take default from layout_map
                $this->viewData = isset($layoutMap[$page->id]['item_view_data']) ? $layoutMap[$page->id]['item_view_data'] : [];//default item view data
                $this->viewData = $this->deserializeValue('viewData');
            }
            $this->dataProvider = $page->dataProvider;
            $this->dataProvider->pagination->pageSize = $this->itemsLimit;
        }
        return $this->dataProvider;        
        
    }

}
