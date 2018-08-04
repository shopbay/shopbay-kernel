<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
Yii::import("common.widgets.susermenu.components.*");
/**
 * Description of SUserMenu
 *
 * @author kwlok
 */
class SUserMenu extends SWidget
{
    CONST LOGIN = 'login';
    CONST LANG  = 'lang';
    CONST SITE  = 'site';
    CONST WELCOME   = 'welcome';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.susermenu.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'susermenu';
    /**
     * The requested menu type; Refer to CONST types
     * @var string 
     */
    public $type;
    /**
     * The menu owner; Can be a guest or logged in user
     * @var SWebUser 
     */
    public $user;
    /**
     * The view page that user is visiting and contains this menu; 
     * @var ShopViewPage 
     */
    public $page;
    /**
     * If the menu is to be merged with other menu; 
     * @var array Array of menu ids
     */
    public $mergeWith = [];
    /**
     * If the menu is displayed off site, such as facebook site and not directly from Shopbay
     * @var boolean 
     */
    public $offSite = false;
    /**
     * The additional css class;
     * @var SWebUser 
     */
    public $cssClass;
    /**
     * Indicate if menu is off canvas; When true, css class 'offcanvas-menu' will be added; 
     * @var SWebUser 
     */
    public $offCanvas = true;
    /**
     * The top section to be inserted before menu
     */
    public $topSection;
    /**
     * The bottom section to be inserted after menu
     */
    public $bottomSection;
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!isset($this->user))
            throw new CException(__CLASS__." User cannot be null");
        
        if (!isset($this->type))
            throw new CException(__CLASS__." Menu type must be specified");
        
        switch ($this->type) {
            case self::WELCOME:
                $this->renderMenu(ucfirst($this->type).'Menu', $this->offCanvas);
                break;
            default:
                $this->render('common.widgets.susermenu.views.index');
                break;
        }
    }    
    /**
     * Render menu object
     */
    public function renderMenu($menuClass,$offCanvas=true)
    {
        if ($menuClass instanceof UserMenu)
            $menuObj = $menuClass;
        else
            $menuObj = new $menuClass($this->user,['iconDisplay'=>$offCanvas,'offCanvas'=>$offCanvas]);//default hide icon for desktop browser menu
        
        $menu = $menuObj->menu;
        foreach ($this->mergeWith as $other) {//we need this to save instantiate WelcomeMenu twice!
            if ($other!=$this->type)//not merging itself
                $menu = array_merge($this->{$other.'Menu'},$menu);
        }
        $this->render('common.widgets.susermenu.views.index',['menu'=>$menu,'mobileButton'=>$menuObj->offCanvas?null:$menuObj->mobileButton]);
    }
    /**
     * Return the final menu merging with others
     * @param type $type
     * @return type
     */
    public function getMenu($type)
    {
        $menu = $this->{$type.'Menu'};
        foreach ($this->mergeWith as $other) {
            if ($type!=$other)//not merging itself
                $menu = array_merge($menu,$this->{$other.'Menu'});
        }
        return $menu;
    }
    
    public function getSiteMenu()
    {
        return (new SiteMenu($this->user,$this->offSite))->menu;
    }
    /**
     * For mobile use
     * @return type
     */
    public function getLoginMenu()
    {
        return (new LoginMenu($this->user))->menu;
    }
    /**
     * @return type
     */
    public function getLangMenu()
    {
        return (new LangMenu($this->user))->menu;
    }
    /**
     * @return type
     */
    public function getWelcomeMenu()
    {
        return (new WelcomeMenu($this->user))->menu;
    }
}