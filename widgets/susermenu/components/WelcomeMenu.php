<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.susermenu.components.UserMenu");
Yii::import("common.widgets.susermenu.components.UserMenuItem");
Yii::import("common.widgets.susermenu.components.LoginMenu");
/**
 * Description of WelcomeMenu
 *
 * @author kwlok
 */
class WelcomeMenu extends UserMenu
{
    public function __construct($user,$config=[]) 
    {
        $this->user = $user;
        $this->loadConfig($config);
        $messagecnt = $user->getUnreadMessageCount(); 
        $messgecntLabel = $messagecnt==0?'':'<span class="message counter">'.$messagecnt.'</span>';
        $this->items[static::$messages] = new UserMenuItem([
            'id'=> static::$messages,
            'label'=>'<span class="mobile-display-only">'.Sii::t('sii','Messages').'</span>'.$messgecntLabel,
            'icon'=>'<i class="fa fa-envelope-o"></i>',
            'url'=>url('messages'),
            'visible'=>$user->isRegistered,
            'cssClass'=>'quickaccess message',
        ]);
        $loginMenu = new LoginMenu($user, [UserMenu::$messages], true, ['iconPlacement'=>$this->offCanvas?'left':'right']);
        $loginMenu->iconPlacement = 'right';
        $this->items[static::$welcome] = new UserMenuItem([
            'id'=> static::$welcome,
            'label'=>$user->getName(),
            'icon'=>$user->isGuest ? '<i class="fa fa-user"></i> ' : CHtml::tag('i',['class'=>'fa avatar'],$user->getAvatar(Image::VERSION_XXXSMALL)),
            'url'=>url('welcome'),
            'visible'=>$user->isAuthenticated,
            'cssClass'=>'quickaccess home',
            'items'=> $loginMenu->menu,
        ]);
    }
    
    public function getMobileButton()
    {
        $button = CHtml::openTag('div',['class'=>'mobile-button mobile-login']);
        if (!$this->user->isGuest)
            $button .= CHtml::link(CHtml::tag('i',['class'=>'fa avatar'],$this->user->getAvatar(Image::VERSION_XXSMALL)),'javascript:void(0);',['onclick'=>'openoffcanvasloginmenu();']);
        $button .= CHtml::closeTag('div');
        return $button;    
    }  
}