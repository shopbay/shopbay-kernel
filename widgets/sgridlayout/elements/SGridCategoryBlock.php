<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.sgridlayout.elements.SGridColumn");
Yii::import("common.widgets.sgridlayout.elements.SGridCateoryBlockTrait");
Yii::import('common.modules.shops.components.ShopPage');
Yii::import('common.modules.shops.components.ShopBrowseMenu');
/**
 * Description of SGridCategoryBlock
 * 
 * Display a finite number of items (default to one page data provider size)
 *
 * @author kwlok
 */
class SGridCategoryBlock extends SGridColumn
{
    use SGridCateoryBlockTrait;
    
    public $type = SGridLayout::CATEGORY_BLOCK;
    /**
     * The category name (is bind to a specific data provider)
     * @var string
     */
    public $category;   
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
                //local settings
                'title'=>$this->title,
                'category'=>$this->category,
                'itemsPerRow'=>$this->itemsPerRow,
                'itemsLimit'=>$this->itemsLimit,
                'viewData'=>$this->viewData,
            ]);
            $this->content = $widget->blockContent;
        }
        else {        
            $this->content = $this->controller->renderPartial('common.widgets.sgridlayout.views._categoryblock',['element'=>$this],true);
        }
    }     
    /**
     * Render category list view based on data provider
     * @return type
     */
    public function renderListView()
    {
        return $this->controller->widget('common.widgets.SListView', [
            'id' => $this->name,
            'dataProvider' => $this->getDataProvider(),
            'htmlOptions' => ['class'=>'catalog-container'],
            'itemView' => $this->itemView,
            'itemsCssClass'=>'items '.$this->itemCssClass,
            'viewData' => $this->viewData,
            'template' => '{items}',
        ],true);      
    }    
    /**
     * Set data provider page size to follow items limit
     * @return type
     */
    public function getDataProvider()
    {
        if (!isset($this->category)){
            $rawData = [
                ['icon'=>'<i class="fa fa-photo"></i>'],
                ['icon'=>'<i class="fa fa-photo"></i>'],
                ['icon'=>'<i class="fa fa-photo"></i>'],
                ['icon'=>'<i class="fa fa-photo"></i>'],
            ];
            //default to 4 items, single row
            $this->dataProvider = new CArrayDataProvider($rawData,['keyField'=>false,'sort'=>false]);
            $this->itemView = 'common.widgets.sgridlayout.views._categoryblock_item';//default item view
        }
        else {
            $page = $this->createCategoryPage();
            $this->dataProvider = $page->dataProvider;
            $this->dataProvider->pagination->pageSize = $this->itemsLimit;
            $layoutMap = $this->layout->theme->getParam('layout_map');
            //logTrace(__METHOD__,$layoutMap[$page->id]);
            $this->itemView = isset($layoutMap[$page->id]['item_view']) ? $this->layout->getThemeView($layoutMap[$page->id]['item_view']) : 'common.widgets.sgridlayout.views._categoryblock_item';//default item view
            //logTrace(__METHOD__.' item view ',$this->itemView);
            if (empty($this->viewData)){//take default from layout_map
                $this->viewData = isset($layoutMap[$page->id]['item_view_data']) ? $layoutMap[$page->id]['item_view_data'] : [];//default item view data
                $this->viewData = $this->deserializeValue('viewData');
            }
        }
        return $this->dataProvider;
    }
    /**
     * Create a new category page to load dataprovider 
     */
    protected function createCategoryPage()
    {
        $filter = [];
        $category = explode(':', $this->category);
        if ($category[0]==ShopPage::TRENDS && isset($category[1])){
            $filter['topic'] = $category[1];//trend topic, refer to ShopWidgets::getDataProvidersList()
        }
        elseif ($category[0]==ShopPage::CATEGORY && isset($category[1])){
            $filter[ShopBrowseMenu::CATEGORY] = $category[1];//trend topic, refer to ShopWidgets::getDataProvidersList()
        }
        elseif ($category[0]==ShopPage::BRAND && isset($category[1])){
            $filter[ShopBrowseMenu::BRAND] = $category[1];//trend topic, refer to ShopWidgets::getDataProvidersList()
        }
        return $this->layout->createPage($category[0],$filter);
    }    
 
}
