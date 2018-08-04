<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.susermenu.components.UserMenu");
Yii::import("common.widgets.susermenu.components.UserMenuItem");
/**
 * Description of LoginMenu
 *
 * @author kwlok
 */
class LoginMenu extends UserMenu
{
    /**
     * Menu constructor
     * @param type $user
     * @param type $excludes
     * @param type $loadItems
     * @param array $config
     */
    public function __construct($user,$excludes=[],$loadItems=true,$config=[]) 
    {
        $this->loadConfig($config);//load options if any
        if ($loadItems){
            $menuClass = $user->currentRole.__CLASS__;
            $menu = new $menuClass($user,$config);
            foreach ($menu->items as $id => $menuitem) {//clone user menu items
                $this->items[$id] = $menuitem;
            }
        }
        //Excludes menu if any
        foreach ($excludes as $menu) {
            if (isset($this->items[$menu]))
                unset($this->items[$menu]);
        }
    }
    
}