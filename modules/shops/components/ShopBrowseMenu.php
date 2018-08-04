<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.BasePage");
/**
 * Description of ShopBrowseMenu
 *
 * @author kwlok
 */
class ShopBrowseMenu extends BasePage 
{
    /*
     * Main Pages
     */
    const CATEGORY = 'category';
    const BRAND    = 'brand';
    
    public $currentPage;//ShopViewPage current page object
    public $useJavascript = true;//True to use javascript "browse()", false to use direct url

    private $_data;//stores menu data
    private $_rawData = [];//stores menu raw data (not CMenu data)
    private $_status = Process::PRODUCT_ONLINE;//Indicate the product status filter; If not set, select all products; Default to select all online products
    /**
     * Get menu list
     * @param type $locale
     * @return type
     */
    public function getMenu($locale)
    {
        if ($this->id==ShopBrowseMenu::CATEGORY){
            $categories = Category::model()->locateShop($this->model->id)->findAll();
            foreach ($categories as $category){
                $categoryName =  $category->displayLanguageValue('name',$locale);
                $this->_rawData[$category->slug] = $categoryName;
                foreach ($category->subcategories as $subcategory) {
                    $this->_rawData[str_replace('/', CategorySub::KEY_SEPARATOR, $subcategory->slug)] = $categoryName.Helper::SINGLE_ARROW_CHAR.$subcategory->displayLanguageValue('name',$locale);
                }
            }
        }
        elseif ($this->id==ShopBrowseMenu::BRAND){
            $brands = Brand::model()->locateShop($this->model->id)->findAll();
            foreach ($brands as $brand){
                $this->_rawData[$brand->slug] = $brand->displayLanguageValue('name',$locale);
            }
        }
        return $this->_rawData;
    }
    
    public function getHasData()
    {
        return isset($this->_data);
    }     
    /**
     * A wrapper method to load data
     */
    public function loadData($locale=null)
    {
        $this->getData($locale);
    }
    
    public function getData($locale=null,$activeItem=null) 
    {
        if (!isset($this->_data)){
            $menu = new CMap();
            foreach ($this->_searchProducts(isset($locale)?$locale:user()->getLocale()) as $record) {
                $totalCount = $record['count'];
                $items = [];
                if (isset($record['items'])){//for subcategories
                    $subitems = [];
                    foreach ($record['items'] as $subitem) {
                        
                        $subitems[] = [
                            'label'=>$subitem['name'].' ('.$subitem['count'].')',
                            'url'=>$this->getMenuUrl($subitem['slug']),
                            'linkOptions' => [
                                'onclick' => 'browse("'.$this->id.'",$(this));',
                                'data-'.$this->id => $subitem['id'],
                                'data-shop' => $this->model->id,
                                'data-facebook' => $this->controller->onFacebook()?Helper::constructUrlQuery('',$this->controller->getFacebookUriParams()):null,
                            ],
                            'active'=>$activeItem==$subitem['id'],
                        ];
                    }
                    $items['items'] = $subitems;
                    //recompute main category item count when there exists subcategory
                    $totalCount = $this->_countCategoryProducts($record['id']);
                }
                
                $items = array_merge($items,[
                    'label'=>$record['name'].' ('.$totalCount.')',
                    'url'=>$this->getMenuUrl($record['slug']),
                    'linkOptions' => [
                        'onclick' => 'browse("'.$this->id.'",$(this));',
                        'data-'.$this->id => $record['id'],
                        'data-shop' => $this->model->id,
                        'data-facebook' => $this->controller->onFacebook()?Helper::constructUrlQuery('',$this->controller->getFacebookUriParams()):null,
                    ],
                    'active'=>$activeItem==$record['id'],
                ]);
                $menu->add($record['name'], $items);
            }
            if ($menu->count()>0){
                //logTrace(__METHOD__.' '.$this->id,$menu->toArray());
                $this->_data = Helper::sortArrayByKey($menu->toArray());
            }
        }
        
        if (isset($activeItem)){
            $this->setActiveItem($this->_data,$activeItem);
        }
        
        return $this->_data;
    }
    
    protected function getMenuUrl($slug)
    {
        $url = $this->model->getUrl($this->currentPage->https).'/'.$this->id.'/'.$slug;
        return $this->useJavascript ? 'javascript:void(0);' : $this->currentPage->appendExtraQueryParams($url);
    }
    /**
     * Set a particular menu item to active
     * @param boolean $data Array is passed by reference
     * @param type $id 
     */
    protected function setActiveItem(&$data,$id)
    {
        if ($this->hasData){
            foreach ($data as $idx => $item) {
                if (isset($item['linkOptions']['data-'.$this->id]) && 
                    $id==$item['linkOptions']['data-'.$this->id]){
                    $data[$idx]['active'] = true;
                    //logTrace(__METHOD__.' '.$id.' selected',$data[$idx]);
                    break;
                }
                if (isset($item['items'])){
                    //scan through subcateogries
                    foreach ($item['items'] as $subidx => $subitem) {
                        if (isset($subitem['linkOptions']['data-'.$this->id]) && 
                            $id==$subitem['linkOptions']['data-'.$this->id]){
                            $data[$idx]['items'][$subidx]['active'] = true;
                            //logTrace(__METHOD__.' '.$id.' selected',$data[$idx]['items'][$subidx]);
                            break;
                        }
                    }
                }

            }
        }
    }
    /**
     * Cound total products belong to a particular category
     * This is useful for main cateogry as some main category products has no subcategory
     * or, subcategory products belong to multiple subcategories and not distinct for main category product count
     * @param type $categoryKey
     * @return type
     */
    private function _countCategoryProducts($categoryKey)
    {            
        return $this->model->searchProductsByCategory($categoryKey,$this->_status)->getTotalItemCount();
    }
    
    private function _searchProducts($locale)
    {
        switch ($this->id) {
            case self::CATEGORY:
                return $this->_searchProductsByCategory($this->_status,$locale);
            case self::BRAND:
                return $this->_searchProductsByBrand($this->_status,$locale);
            default:
                return [];
        }
    }
    /**
     * Search shop products by category
     * @param type $status
     * @param type $locale
     * @return type
     */
    private function _searchProductsByCategory($status=null, $locale=null)
    {
        $where = 'p.shop_id='.$this->model->id;
        if (isset($status))
            $where .= ' AND p.status=\''.$status.'\'';
        $command = Yii::app()->db->createCommand()
                        ->select('c.id, c.name, c.slug, SUM(1) count')         
                        ->from(Product::model()->tableName().' p')
                        ->join(ProductCategory::model()->tableName().' pc', 'pc.product_id = p.id')
                        ->join(Category::model()->tableName().' c', 'pc.category_id = c.id')
                        ->group('c.id')
                        ->where($where);
        logTrace(__METHOD__.' query command = '.$command->text);
        $results = $command->queryAll();
        //search subcategories
        foreach ($results as $idx => $result) {
            $results[$idx]['items'] = $this->_searchProductsBySubcategory($status, $locale, $results[$idx]['id']);
        }
        return $this->_parseLocaleValue($locale, $results, 'Brand');
    }       
    /**
     * Search shop products by category
     * @param type $status
     * @param type $locale
     * @return type
     */
    private function _searchProductsBySubcategory($status=null, $locale=null, $cateogry_id=null)
    {
        $where = 'p.shop_id='.$this->model->id;
        if (isset($status))
            $where .= ' AND p.status=\''.$status.'\'';
        
        $productCategoryWhere = 'pc.product_id = p.id AND pc.subcategory_id IS NOT NULL';
        if (isset($cateogry_id))
            $where .= ' AND pc.category_id='.$cateogry_id;

        $command = Yii::app()->db->createCommand()
                        ->select('concat(c.id,\''.CategorySub::KEY_SEPARATOR.'\',sc.id) as id, sc.name, concat(c.slug,\'/\',sc.slug) as slug, SUM(1) count')               
                        ->from(Product::model()->tableName().' p')
                        ->join(ProductCategory::model()->tableName().' pc', $productCategoryWhere)
                        ->join(CategorySub::model()->tableName().' sc', 'sc.category_id = pc.category_id AND sc.id = pc.subcategory_id')
                        ->join(Category::model()->tableName().' c', 'c.id = pc.category_id AND c.id = sc.category_id')
                        ->group('sc.id')
                        ->where($where);
        logTrace(__METHOD__.' query command = '.$command->text);
        $results = $command->queryAll();
        return $this->_parseLocaleValue($locale, $results, 'CategorySub');
    }           
    /**
     * Search shop products by brand
     * @param type $group either brand or category id
     * @param type $status
     * @param type $locale
     * @return type
     */
    private function _searchProductsByBrand($status=null, $locale=null)
    {
        $where = 'p.brand_id IS NOT NULL AND p.shop_id='.$this->model->id;
        if (isset($status))
            $where .= ' AND p.status=\''.$status.'\'';
        $command = Yii::app()->db->createCommand()
                        ->select('g.name, p.brand_id id, g.slug, SUM(1) count')               
                        ->from(Product::model()->tableName().' p')
                        ->join(Brand::model()->tableName().' g', 'p.brand_id = g.id')
                        ->group('p.brand_id')
                        ->where($where);
        logTrace(__METHOD__.' query command = '.$command->text);
        $results = $command->queryAll();
        return $this->_parseLocaleValue($locale, $results, 'Brand');
    }           
    /**
     * If locale presents, extract $result['name'] based on locale value
     * @param type $locale
     */
    private function _parseLocaleValue($locale,&$results,$type)
    {
        if (isset($locale)){
            foreach ($results as $idx => $result) {
                $results[$idx]['name'] = $this->model->parseLanguageValue($result['name'],$locale);
                //fallback to default locale if value not set
                if ($results[$idx]['name']==Sii::t('sii','unset')){
                    $results[$idx]['name'] = $this->model->parseLanguageValue($result['name'],$this->model->getLanguageDefaultLocale());
                }
            }
        }    
        return $results;
    }
    
    public function getLabel()
    {
        return self::getLabels($this->id);
    }
    
    public static function getLabels($key=null)
    {
        if (!isset($key)){
            return [
                self::CATEGORY => Sii::t('sii','Categories'),
                self::BRAND => Sii::t('sii','Brands')
            ];
        }
        else {
            $labels = self::getLabels();
            return $labels[$key];
        }
    }
    
    public static function getLabelKeys($name)
    {
        return array_search($name,array_keys(self::getLabels()));
    }    
        
}
