<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.BasePage");
Yii::import("common.modules.shops.components.ShopPage");
Yii::import("common.modules.shops.components.FacebookShopTrait");
Yii::import("common.modules.pages.models.Page");
/**
 * Description of ShopNavigation
 *
 * @author kwlok
 */
class ShopNavigation extends BasePage 
{    
    use FacebookShopTrait;
    
    public static $typeCustomPage  = 'custompage';
    public static $typeCategory    = 'category';
    public static $typeSubcategory = 'subcategory';
    public static $typeLink        = 'link';
    
    public $page;
    
    protected $useWidget = 'Nav';//either SNavigationMenu or bootstrap Nav
    
    public function getSourceData($locale=null)
    {
        $source = json_decode($this->model->mainMenu,true);
        if (empty($source)){//no data found, use back default main menu
            $source = $this->model->getDefaultMainMenu(true);
        }
        return $source;
    }
    
    public function getData($locale=null) 
    {
        return $this->constructMenu($this->getSourceData($locale));
    }
    /**
     * Transform menu to fit into widget SNavigationMenu
     * @param type $source The menu source data stored at db
     * @return type
     */
    protected function constructMenu($source=[])
    {
        $menu = [];
        foreach ($source as $data) {
            if (is_array($data) && !empty($data)){
                $menu = $this->addMenuItem($menu, $data, 1);//level 1
            }
        }
        return $menu;
    }
    /**
     * Level 2 menu
     * @param type $data
     * @return array
     */
    public function constructSubmenu($data)
    {
        $submenu = [];
        if (isset($data['items'])){
            foreach ($data['items'] as $subdata) {
                $submenu = $this->addMenuItem($submenu, $subdata, 2);//level 2
            }
        }
        return $submenu;
    }
    
    protected function addMenuItem($menu,$data,$level)
    {
        switch ($data['type']) {
            case static::$typeCategory:
                $menu[] = $this->getCategoryMenuItem($data,$level);
                break;
            case static::$typeSubcategory:
                $menu[] = $this->getSubcategoryMenuItem($data,$level);
                break;
            case static::$typeLink:
                $menu[] = $this->getLinkMenuItem($data,$level);
                break;
            case static::$typeCustomPage:
                $menu[] = $this->getCustomPageMenuItem($data,$level);
                break;
            default:
                break;
        }    
        return $menu;
    }
    
    protected function getLinkMenuItem($data,$level)
    {
        return $this->getMenuItem($data['id'], $data['heading'][user()->getLocale()], $data['url'], $this->constructSubmenu($data),$this->getLinkOptions($level));
    }
    
    protected function getCustomPageMenuItem($data,$level)
    {
        $page = Page::model()->findByPk(static::decodeId($data['id'],static::$typeCustomPage));
        if ($page!=null){
            return $this->getMenuItem($data['id'], $page->displayLanguageValue('title',user()->getLocale()), $this->constructUrl($page), $this->constructSubmenu($data),$this->getLinkOptions($level));
        }
        else 
            return null;
    }
    
    protected function getCategoryMenuItem($data,$level)
    {
        $category = Category::model()->findByPk(static::decodeId($data['id'],static::$typeCategory));
        if ($category!=null){
            return $this->getMenuItem($data['id'], $category->displayLanguageValue('name',user()->getLocale()), $this->constructUrl($category), $this->constructSubmenu($data),$this->getLinkOptions($level));
        }
        else 
            return null;
    }
    
    protected function getSubcategoryMenuItem($data,$level)
    {
        $subcategory = CategorySub::model()->findByPk(static::decodeId($data['id'],static::$typeSubcategory));
        if ($subcategory!=null)
            return $this->getMenuItem($data['id'], $subcategory->displayLanguageValue('name',user()->getLocale()), $this->constructUrl($subcategory),[],$this->getLinkOptions($level));
        else 
            return null;
    }
    
    protected function getMenuItem($id,$label,$url,$items=[],$linkOptions=[])
    {
        if ($this->useWidget=='SNavigationMenu'){
            return [
                'label'=>$label,
                'url'=>$url,
                'active'=>$this->getActiveCondition($id),
                'itemOptions'=>['id'=>$id.'_navitem','class'=>'navmenuitem'],
                'linkOptions' => $linkOptions,
                'items'=>$items,
            ];
        }
        elseif ($this->useWidget=='Nav'){
            //Tip: to enable Level 1 link clickable
            if (isset($linkOptions['data-level']) && $linkOptions['data-level']==1){
                $linkOptions['class'] = 'disabled';
            }
            $menuitem = [
                'label'=>$label,
                'url'=>$url,
                'options'=>['class'=>$this->getActiveCondition($id)],
                'linkOptions' => $linkOptions,
            ];
            if (!empty($items))//to avoid empty dropdown submenu
                $menuitem = array_merge($menuitem,['items'=>$items]);
            return $menuitem;
        }
        else
            return [];
    }
    /**
     * @todo Review this code fragment if still required?
     * Seems old logic?
     * @param type $page
     * @return boolean
     */
    protected function getActiveCondition($page)
    {
        switch ($this->page->id) {
            //Member pages shared the same Home page link (or under home page)
            case ShopPage::PRODUCT:
            case ShopPage::CAMPAIGN:
            case ShopPage::SEARCH:
                if ($this->page->id==$page)//cater for if member page also becomes main menu page
                    return true;
                else 
                    return $page==ShopPage::defaultPage();//default page
            default:
               return $this->page->id==$page;
        }
    }
    /**
     * Construct nav url with conditions check
     * @param CModel $model The model used to build the url
     * @return string
     */
    protected function constructUrl($model)
    {
        $url = $model->getUrl($this->page->https);
        return $this->page->appendExtraQueryParams($url);
    }
    
    protected function getLinkOptions($level)
    {
        return ['data-level'=>$level];  
    }
    /**
     * Default menu content
     * Menu item data formmat:
     * [
     *  [ 
     *   'id'=>'','type'=>'','heading'=>'','url'=>'',items=>[<..submenu.>],
     *  ],
     *  [ 
     *   'id'=>'','type'=>'','heading'=>'','url'=>'',items=>[<..submenu.>],
     *  ]
     * ]
     * @param type $array
     * @param type $mandatoryMenuItem 
     * @return array
     */
    public function getDefaultMainMenu($array=false,$mandatoryMenuItem=ShopPage::PRODUCTS)
    {
        if (!isset($model))
            $shopModel = $this->model;
        
        $menu = [];
        $pages = [
            $mandatoryMenuItem,
            ShopPage::TRENDS,
            ShopPage::PROMOTIONS,
            ShopPage::NEWS,
            ShopPage::ABOUT,
        ];
        foreach ($pages as $page) {
            $pageModel = Page::model()->locateOwner($this->model)->active()->locatePage($page)->find();
            if ($pageModel!=null) {
                $menu[] = ['id'=>self::encodeId($pageModel->id, static::$typeCustomPage),'type'=>static::$typeCustomPage,'items'=>[]];
            }
        }
        
        if ($array)
            return $menu;
        else
            return json_encode($menu);
    }            
    /**
     * Check if shop page is offsite (accessed at third party site)
     * @return type
     */
    public static function isOffSite() 
    {
        $page = new ShopNavigation('dummy', null, null);
        return $page->onFacebook();//For now Facebook is the only supported offsite
    }
    /**
     * Encode id with suffix using type
     * @param type $id
     * @param type $type
     * @return type
     */
    public static function encodeId($id,$type)
    {
        return $type.'_'.$id;
    }    
    /**
     * Remove suffix and return the numeric id value
     * @param type $id
     * @param type $type
     * @return type
     */
    public static function decodeId($id,$type)
    {
        return ltrim($id, $type.'_');
    }     
        
}
