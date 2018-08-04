<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.pages.models.Page');
Yii::import('common.modules.shops.components.ShopNavigation');
Yii::import('common.modules.shops.models.NavigationLinkForm');
/**
 * Description of NavigationSettingsTrait
 * Menu item data formmat:
 * [
 *  [ 
 *   'id'=>'','type'=>'','heading'=>'','url'=>'',items=>[<..submenu.>],
 *  ],
 *  [ 
 *   'id'=>'','type'=>'','heading'=>'','url'=>'',items=>[<..submenu.>],
 *  ]
 * ]
 * @author kwlok
 */
trait NavigationSettingsTrait 
{
    public $mainMenu;
    /**
     * The limit technically is UI issue that too many items might cause UI too messy or out of alignment
     * @todo This param should let each theme handles it as some theme might can accept many menu items, some might not.
     * @return type
     */
    public function getMainMenuLimit()
    {
        return Config::getBusinessSetting('limit_navitem');
    }
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['mainMenu', 'ruleMainMenu'],
            ['mainMenu', 'length', 'max'=>5000],
            //todo validate link url and name 
        ]);
    }    
    /**
     * Main menu validation rule
     * If not set, always follow default main menu
     * @param type $attribute
     * @param type $params
     */
    public function ruleMainMenu($attribute,$params)
    {
        if (empty($this->mainMenu))
            $this->mainMenu = $this->getDefaultMainMenu();
        
        $menus = $this->getMainMenu(true);
        $validateLink = function($menu,$index,$attribute) {
            $form = new NavigationLinkForm($this->owner->id);
            foreach ($menu['heading'] as $locale => $heading) {
                $form->title = $heading;
                if (!$form->validate(['title'])){
                    logError(__METHOD__.' link menu name errors',$form->getErrors('title'));
                    $this->addError($index.$attribute.$menu['id'], Sii::t('sii','Menu item #{index}: {error}',['{index}'=>$index,'{error}'=>$form->getErrors('title')[0]]));
                }
            }
            $form->link = $menu['url'];
            if (!$form->validate(['link'])){
                logError(__METHOD__.' link menu url errors',$form->getErrors('link'));
                $this->addError($index.$attribute.$menu['id'], Sii::t('sii','Menu item #{index}: {error}',['{index}'=>$index,'{error}'=>$form->getErrors('link')[0]]));
            }
        };
        //validate menu links only (rest menu items are built-in types, no need validation
        foreach ($menus as $index => $menu) {
            if ($menu['type']==ShopNavigation::$typeLink){
                $validateLink($menu,($index+1),$attribute);
            }
            if (isset($menu['items'])){
                foreach ($menu['items'] as $subindex => $submenu) {
                    if ($submenu['type']==ShopNavigation::$typeLink)
                        $validateLink($submenu,($index+1).'.'.($subindex+1),$attribute);
                }
            }
        }
    }    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::rules(),[
            'mainMenu' => Sii::t('sii','Main Menu'),
        ]);
    }
    /**
     * @return array customized attribute display values (name=>label)
     */
    public function attributeDisplayValues()
    {
        return array_merge(parent::attributeDisplayValues(),[
            'mainMenu'=>Helper::htmlList(array_values($this->getMainMenuItems()),['class'=>'navigation-menuitems']),
        ]);
    }     
    
    public function getMainMenuContainer()
    {
        return 'main_menu_container';
    }
    
    public function getMainMenu($array=false)
    {
        if (isset($this->mainMenu)){
            if ($array)
                return json_decode($this->mainMenu,true);
            else
                return $this->mainMenu;
        }
        else
            return $this->getDefaultMainMenu($array);
    }    
    /**
     * Mandatory menu item must exists in nav menu
     */
    public function getMandatoryMenuItem()
    {
        return ShopPage::PRODUCTS;
    }
    /**
     * Default main menu
     * @see ShopNavigation
     */
    public function getDefaultMainMenu($array=false)
    {
        $nav = new ShopNavigation('DUMMY',$this->owner,null);//set controller to null (no use)
        return $nav->getDefaultMainMenu($array,$this->mandatoryMenuItem);
    }         
    /**
     * Get custom pages that are not selected
     * @return type
     */
    public function getNonSelectedCustomPages()
    {
        $menuItems = new CMap();
        foreach (Page::model()->locateOwner($this->owner)->active()->findAll() as $page) {
            $found = false;
            if ($page->isFullPage){
                foreach ($this->getMainMenu(true) as $menu) {
                    if (isset($menu['id']) && $page->id==ShopNavigation::decodeId($menu['id'],ShopNavigation::$typeCustomPage) && $menu['type']==ShopNavigation::$typeCustomPage){
                        $found = true;//page is currently a main menu item
                        break;
                    }
                    //loop through submenu to check
                    if (isset($menu['items']) && !empty($menu['items'])){
                        foreach ($menu['items'] as $subitem) {
                            if (isset($subitem['id']) && $page->id==ShopNavigation::decodeId($subitem['id'],ShopNavigation::$typeCustomPage)  && $subitem['type']==ShopNavigation::$typeCustomPage){
                                $found = true;//page is currently a sub menu item
                                break;
                            }
                        }
                    }
                }
                if (!$found){
                    $menuItems->add(ShopNavigation::encodeId($page->id,ShopNavigation::$typeCustomPage),$this->_getMenuItemHtml($page->displayLanguageValue('title',user()->getLocale()),ShopNavigation::$typeCustomPage));
                }
            }
        }
        //logTrace(__METHOD__,$menuItems);
        return $menuItems->toArray();
    }      
    /**
     * Get categories that are not selected
     * @return type
     */
    
    public function getNonSelectedCategories()
    {
        $menuItems = new CMap();
        //use a big page size '1000' to include all setup categories
        foreach ($this->owner->searchCategories(null,1000)->data as $category) {
            $found = false;
            foreach ($this->getMainMenu(true) as $menu) {
                if (isset($menu['id']) && $category->id==ShopNavigation::decodeId($menu['id'],ShopNavigation::$typeCategory) && $menu['type']==ShopNavigation::$typeCategory){
                    $found = true;//category is currently a main menu item
                    break;
                }
                //no need to loop through submenu to check, since category represents the whole set
            }
            if (!$found){
                $categoryItem = $this->_getMenuItemHtml($category->displayLanguageValue('name',user()->getLocale()),ShopNavigation::$typeCategory);
                if ($category->hasSubcategories()){
                    $categoryItem .= CHtml::openTag('ul',['class'=>'ui-sortable submenu']);
                }
                foreach ($category->subcategories as $index => $subcategory) {
                    $categoryItem .= CHtml::tag('li',['id'=>ShopNavigation::encodeId($subcategory->id,ShopNavigation::$typeSubcategory),'type'=>ShopNavigation::$typeSubcategory],$subcategory->displayLanguageValue('name',user()->getLocale()));
                }
                if ($category->hasSubcategories()){
                    $categoryItem .= CHtml::closeTag('ul');
                }
                $menuItems->add(ShopNavigation::encodeId($category->id, ShopNavigation::$typeCategory),$categoryItem);
            }
        }
        //logTrace(__METHOD__,$menuItems);
        return $menuItems->toArray();
    }    
    /**
     * Page object, e.g. 'ShopPage' 
     * @return string
     */
    public function getPageObject()
    {
        throw new CException('Please define page object');
    }
    /**
     * Specify which navigation types to include
     * @return type
     */
    public function getIncludeNavigationTypes()
    {
        return [];
    }    
    /**
     * Check if to support the navigation type
     * @return boolean
     */
    public function existsType($type)
    {
        return in_array($type, $this->getIncludeNavigationTypes());
    }
    /**
     * Enable Mandatory menu item check
     * @return string Must only value "true" or "false"
     */
    public function getCheckMandatoryMenuItem()
    {
        return 'false';
    }
    /**
     * Load navigation menu items from db; If not set, load default
     * @see getDefaultMainMenu()
     * @return type
     */
    public function getMainMenuItems()
    {
        $pageObject = $this->getPageObject();
        $navMenu = new CMap();
        foreach ($this->getMainMenu(true) as $menu) {
            if (!empty($menu) && is_array($menu)){
                
                switch ($menu['type']) {
                    case ShopNavigation::$typeCategory:
                        $menuItem = $this->_getCategoryMenuHtml($menu);
                        //submenu not allowed
                        break;
                    case ShopNavigation::$typeLink:
                        $menuItem = $this->_getLinkMenuHtml($menu);
                        $menuItem .= $this->getSubmenuItems($menu);//if any
                        break;
                    case ShopNavigation::$typeCustomPage:
                        $pageId = ShopNavigation::decodeId($menu['id'],ShopNavigation::$typeCustomPage);
                        $page = Page::model()->findByPk($pageId);
                        if ($page!=null)
                            $menuItem = $this->_getMenuItemHtml($page->displayLanguageValue('title',user()->getLocale()),ShopNavigation::$typeCustomPage);
                        $menuItem .= $this->getSubmenuItems($menu);//if any
                        break;
                    default:
                        break;
                }
                $navMenu->add($menu['id'],$menuItem);
            }
        }
        return $navMenu->toArray();
    }
    
    public function getMainMenuLimitMessage()
    {
        return Sii::t('sii','Navigation Menu allows maximum {limit} pages only.',['{limit}'=>$this->mainMenuLimit]);
    }
    
    public function getNonMainMenuCheckMessage()
    { 
        $pageObject = $this->getPageObject();
        return Sii::t('sii','Navigation Menu must contain {page}.',['{page}'=>$pageObject::getTitle($this->mandatoryMenuItem)]);
    }    
    /**
     * Menu item html 
     * @param type $menuName
     * @param type $type Menu item
     * @return type
     */
    private function _getMenuItemHtml($menuName,$type)
    {
        return CHtml::tag('div',['class'=>'sort-item '.$type,'data-type'=>$type],'<i class="fa fa-arrows"></i>'.self::getMenuIcon($type).$menuName);
    }

    private function _getCategoryMenuHtml($menu,$submenuClass='submenu')
    {
        $html = '';
        $category = Category::model()->findByPk(ShopNavigation::decodeId($menu['id'],ShopNavigation::$typeCategory));
        if ($category!=null)
            $html = $this->_getMenuItemHtml($category->displayLanguageValue('name',user()->getLocale()),ShopNavigation::$typeCategory);
        if (isset($menu['items'])){//submenu exists
            $html .= CHtml::openTag('ul',['class'=>'ui-sortable '.$submenuClass]);
            foreach ($menu['items'] as $submenu) {
                $subcategory = CategorySub::model()->findByPk(ShopNavigation::decodeId($submenu['id'],ShopNavigation::$typeSubcategory));
                if ($subcategory!=null){
                    //Note that category submenu is not sortable or draggable
                    $html .= CHtml::tag('li',['id'=>ShopNavigation::encodeId($subcategory->id, ShopNavigation::$typeSubcategory),'type'=>ShopNavigation::$typeSubcategory],$subcategory->displayLanguageValue('name',user()->getLocale()));
                }
            }
            $html .= CHtml::closeTag('ul');
        }
        return $html;
    }
    
    private function _getLinkMenuHtml($menu)
    {
        $form = new NavigationLinkForm($this->owner->id);
        $form->title = json_encode($menu['heading']);
        $form->link = $menu['url'];
        $widget = Yii::app()->controller->renderPartial('shops.views.settings._form_navigation_link',['model'=>$form],true);
        $html = $this->_getMenuItemHtml($widget,ShopNavigation::$typeLink);
        return $html;
    }
    
    private function _getSubMenuItemHtml($submenuId,$items=[])
    {
        return Yii::app()->controller->widget('zii.widgets.jui.CJuiSortable',[
                'id'=>$submenuId,
                'htmlOptions'=>['class'=>'connectedSortable submenu'],
                'items'=>$items,
                'options'=>[
                    'delay'=>'300',
                    'connectWith'=>".connectedSortable",
                    'receive'=>new CJavaScriptExpression("function(event, ui){sortablereceivesubmenu(ui);}"),
                    'update'=>new CJavaScriptExpression("function(event, ui){updateheadermenusettings($('#$this->mainMenuContainer'));}"),
                    'remove'=>new CJavaScriptExpression("function(event, ui){updateheadermenusettings($('#$this->mainMenuContainer'));}"),
                ],
            ], true);
    }
    /**
     * @return The form view file to be rendered
     */
    public function formViewFile()
    {
        return 'shops.views.settings._form_navigation';       
    }         
    
    protected function getSubmenuItems($menu)
    {
        $pageObject = $this->getPageObject();
        $submenuItem = '';
        if (isset($menu['items'])){//submenu exists
            $submenuItems = [];
            foreach ($menu['items'] as $submenu) {
                //determine menuname for page type
                if ($submenu['type']==ShopNavigation::$typeCustomPage){
                    $page = Page::model()->findByPk(ShopNavigation::decodeId($submenu['id'],ShopNavigation::$typeCustomPage));
                    if ($page!=null)
                        $menuName = $page->displayLanguageValue('title',user()->getLocale());
                }
                else
                    $menuName = Sii::t('sii','unset');//should not happen!
                
                //Form submenu items
                if ($submenu['type']==ShopNavigation::$typeCategory)
                    $submenuItems[$submenu['id']] = $this->_getCategoryMenuHtml($submenu,'subsubmenu');//3rd level menu
                elseif ($submenu['type']==ShopNavigation::$typeLink)
                    $submenuItems[$submenu['id']] = $this->_getLinkMenuHtml($submenu);
                else
                    $submenuItems[$submenu['id']] = $this->_getMenuItemHtml($menuName,$submenu['type']);
            }            
            $submenuItem = $this->_getSubMenuItemHtml($menu['id'].'_submenu',$submenuItems);
        }
        return $submenuItem;
    }
    
    public function getNavLinkWidget($controller)
    {
        $menuItems = new CMap();
        $widget = $controller->renderPartial('shops.views.settings._form_navigation_link',['model'=>new NavigationLinkForm($this->owner->id)],true);
        $menuItems->add(ShopNavigation::$typeLink.'_1',$this->_getMenuItemHtml($widget, ShopNavigation::$typeLink));
        return $menuItems;
    }
    
    public function getNavCandidatesSectionData($controller)
    {
        $onReceive = new CJavaScriptExpression("function(event, ui){sortablereturn(event,ui,'$this->nonMainMenuCheckMessage','$this->mandatoryMenuItem',$this->checkMandatoryMenuItem);}");
        $sections = new CList();
        //section 1: ShopNavigation::$typeCategory
        if ($this->existsType(ShopNavigation::$typeCategory)){
            $sections->add([
                'id'=>ShopNavigation::$typeCategory,
                'name'=>static::getMenuIcon(ShopNavigation::$typeCategory).Sii::t('sii','Categories'),
                'heading'=>true,'top'=>true,
                'html'=>$controller->widget('zii.widgets.jui.CJuiSortable',[
                    'htmlOptions'=>['class'=>'connectedSortable '.ShopNavigation::$typeCategory],
                    'items'=>$this->nonSelectedCategories,
                    'options'=>[
                        'delay'=>'300',
                        'connectWith'=>".connectedSortable",
                        'receive'=>$onReceive,
                    ],
                ],true),
            ]);
        }
        //section 2: $typeCustomPage
        if ($this->existsType(ShopNavigation::$typeCustomPage)){
            $sections->add([
                'id'=>ShopNavigation::$typeCustomPage,
                'name'=>static::getMenuIcon(ShopNavigation::$typeCustomPage).Sii::t('sii','Pages'),
                'heading'=>true,
                'html'=>$controller->widget('zii.widgets.jui.CJuiSortable',[
                    'htmlOptions'=>['class'=>'connectedSortable '.ShopNavigation::$typeCustomPage],
                    'items'=>$this->nonSelectedCustomPages,
                    'options'=>[
                        'delay'=>'300',
                        'connectWith'=>".connectedSortable",
                        'receive'=>$onReceive,
                    ],
                ],true),
            ]);
        }
        //section 3: ShopNavigation::$typeLink
        if ($this->existsType(ShopNavigation::$typeLink)){
            $sampleNavLink = new NavigationLinkForm($this->owner->id);
            $sampleNavLink->setAsSample();
            $sampleLinkHtml = $controller->renderPartial('shops.views.settings._form_navigation_link',['model'=>$sampleNavLink],true);
            $sections->add([
                'id'=>ShopNavigation::$typeLink,
                'name'=>static::getMenuIcon(ShopNavigation::$typeLink).Sii::t('sii','Links').
                        CHtml::link(Sii::t('sii','Add'),'javascript:void(0);',['class'=>'add-nav-link','onclick'=>'addnavlink();']),
                'heading'=>true,
                'html'=>CHtml::tag('div',['class'=>'nav-link-sample','style'=>'display:none'],$sampleLinkHtml).
                        $controller->widget('zii.widgets.jui.CJuiSortable',[
                            'htmlOptions'=>['class'=>'connectedSortable '.ShopNavigation::$typeLink],
                            'items'=>$this->getNavLinkWidget($controller),
                            'options'=>[
                                'delay'=>'300',
                                'connectWith'=>".connectedSortable",
                                'receive'=>$onReceive,
                            ],
                        ],true),
            ]);        
        }
        return $sections->toArray();        
    }
    
    protected function defaultSubmitFormScript() 
    {
        return 'submitnavmenuform("settings_form","'.$this->mainMenuContainer.'");';
    }   
    
    public static function getMenuIcon($type)
    {
        switch ($type) {
            case ShopNavigation::$typeCategory:
                return '<i class="fa fa-sitemap"></i>';
            case ShopNavigation::$typeLink:
                return '<i class="fa fa-chain"></i>';
            case ShopNavigation::$typeCustomPage:
                return '<i class="fa fa-file-text-o"></i>';
            default:
                return '<i class="fa fa-header"></i>';
        }
    }
    
}
