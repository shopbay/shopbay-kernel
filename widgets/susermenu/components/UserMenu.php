<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of UserMenu
 *
 * @author kwlok
 */
class UserMenu extends CComponent
{
    public static $messages  = 'messages';
    public static $logout    = 'logout';
    public static $login     = 'login';
    public static $register  = 'register';
    public static $cart      = 'cart';
    public static $lang      = 'lang';
    public static $site      = 'site';
    /**
     * Welcome menu only needed for Desktop browser
     */
    public static $welcome = 'welcome';
    /**
     * Account menu only needed for Shopbay account
     */
    public static $account = 'account';
    /**
     * Local property
     */
    public $items = [];
    public $iconPlacement = 'left';
    public $iconDisplay = true;
    public $offCanvas = false;
    public $user;
    /**
     * Get menu and all menu items
     * @return array
     */
    public function getMenu()
    {
        $menu = [];
        foreach ($this->items as $id => $menuitem) {
            $menu[]= $menuitem->toArray();
        }    
        return $menu;
    }
    
    protected function loadConfig($config) 
    {
        if (!empty($config)){//load $config if any
            foreach ($config as $field => $value) {
                if (property_exists($this, $field))
                    $this->$field = $value;
            }
        }
    }
    
    protected function isMenuActive($routePattern)
    {
        $routeUniqueId = Yii::app()->controller->uniqueId.'/'.Yii::app()->controller->action->id;        
        
        if (is_array($routePattern)){
            //[1] absolute route name check
            foreach ($routePattern as $pattern) {
                if ($routeUniqueId==$pattern){
                    return true;
                }
            }
            //[2] or, unique route name check
            foreach ($routePattern as $pattern) {
                if (Yii::app()->controller->uniqueId==$pattern){
                    return true;
                }
            }
            return false;
        }
        else
            return $routeUniqueId==$routePattern;
    }    
    /**
     * Only show login and signup when it is not on login / sign up page
     * @return boolean
     */
    static function includeSiteMenu()
    {
        $include = true;
        $nonQualified = [
            'account\/authenticate\/login','account\/signup',
            'signup','signin'
        ];
        foreach ($nonQualified as $regExp) {
            if (preg_match('/\b'.$regExp.'\b/', request()->getUrlReferrer())) {
                $include = false;
                break;
            }
            if (preg_match('/\b'.$regExp.'\b/', request()->getUrl())) {
                $include = false;
                break;
            }
        }
        return $include;
    }    
}