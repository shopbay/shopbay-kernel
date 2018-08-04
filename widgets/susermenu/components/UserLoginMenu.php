<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.susermenu.components.UserMenu");
Yii::import("common.widgets.susermenu.components.UserMenuItem");
/**
 * Description of UserLoginMenu
 *
 * @author kwlok
 */
abstract class UserLoginMenu extends UserMenu 
{
    public static $dashboard = 'dashboard';
    public static $orders    = 'orders';
    public static $profile   = 'profile';
    public static $shops     = 'shops';
    public static $customers = 'customers';
    public static $help      = 'help';
    /**
     * Menu constructor
     * @param type $user
     * @param array $config
     */
    public function __construct($user,$config=[]) 
    {
        $this->user = $user;
        $this->loadConfig($config);
    }
    
    public function getMessageMenu()
    {
        $messagecnt = $this->user->getUnreadMessageCount(); 
        $messgecntLabel = $messagecnt==0?'':'<span class="message counter">'.$messagecnt.'</span>';
        return new UserMenuItem([
            'id'=> static::$messages,
            'label'=>Sii::t('sii','Messages').$messgecntLabel,
            'icon'=>'<i class="fa fa-fw fa-envelope-o"></i>',
            'iconPlacement'=>$this->iconPlacement,
            'url'=>url('messages'),
            'visible'=>$this->user->isRegistered,
            'cssClass'=>'quickaccess message',
        ]);        
    }
    
    public function getProfileMenu()
    {
        return new UserMenuItem([
            'id'=> static::$profile,
            'label'=>Sii::t('sii','My Profile'),
            'icon'=>'<i class="fa fa-fw fa-user"></i>',
            'iconPlacement'=>$this->iconPlacement,
            'url'=>url('account/profile'),
            'visible'=>$this->user->isRegistered,
            'items'=>$this->getProfileMenuItems(),
        ]);        
    }
    
    public function getAccountMenu()
    {
        return new UserMenuItem([
            'id'=> static::$account,
            'label'=>Sii::t('sii','Manage Account'),
            'icon'=>'<i class="fa fa-fw fa-gear"></i>',
            'iconPlacement'=>$this->iconPlacement,
            'url'=>url('account'),
            'visible'=>$this->user->isRegistered,
            'items'=>$this->getAccountMenuItems(),
        ]);          
    }
    
    public function getLogoutMenu()
    {
        return new UserMenuItem([
            'id'=> static::$logout,
            'label'=>Sii::t('sii','Logout'),
            'icon'=>'<i class="fa fa-fw fa-sign-out"></i>',
            'iconPlacement'=>$this->iconPlacement,
            'url'=>'javascript:void(0)',
            'onclick'=>'logout();',
            'visible'=>$this->user->isAuthenticated,
        ]);        
    }
    
    abstract public function getProfileMenuItems();
    abstract public function getAccountMenuItems();
}
