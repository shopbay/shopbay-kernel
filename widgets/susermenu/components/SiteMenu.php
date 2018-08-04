<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.susermenu.components.UserMenu");
Yii::import("common.widgets.susermenu.components.UserMenuItem");
/**
 * Description of SiteMenu
 *
 * @author kwlok
 */
class SiteMenu extends UserMenu
{
    public $signinScript;
    public $signupScript;
    public $signinUrl = 'javascript:void(0);';
    public $signupUrl = 'javascript:void(0);';
    public $signinLabel;
    public $signupLabel;
    /**
     * Constructor
     * @param type $user
     * @param boolean $offSite If the site menu is displayed from third party site such as Facebook
     * @param array $config
     */
    public function __construct($user,$offSite=false,$config=[]) 
    {
        $this->loadConfig($config);//load options if any
        
        if (!isset($this->signinLabel))
            $this->signinLabel = Sii::t('sii','Log in');
        if (!isset($this->signupLabel))
            $this->signupLabel = Sii::t('sii','Register');
        
        $this->items[static::$login] = new UserMenuItem([
            'id'=> static::$login,
            'label'=>$this->signinLabel,
            'icon'=>'<i class="fa fa-sign-in"></i>',
            'iconDisplay'=>$this->iconDisplay,
            'url'=>$this->signinUrl,
            'onclick'=>isset($this->signinScript) ? $this->signinScript : $this->getSigninScript($offSite),
            'visible'=>$user->isGuest,
            'cssClass'=>'login-menu-item',
        ]);
        
        $this->items[static::$register] = new UserMenuItem([
            'id'=> static::$register,
            'label'=>$this->signupLabel,
            'icon'=>'<i class="fa fa-user"></i>',
            'iconDisplay'=>$this->iconDisplay,
            'url'=>$this->signupUrl,
            'onclick'=>isset($this->signupScript) ? $this->signupScript : $this->getSignupScript($offSite),
            'visible'=>$user->isGuest,
            'cssClass'=>'register-menu-item',
        ]);        
    }    
    /**
     * Dnaymic signin script
     * @param type $offSite True when page is loaded outside Shopbay, such as facebook
     * @return type
     */
    public function getSigninScript($offSite=false)
    {
        return $offSite ? 'newwindowpage("'.Yii::app()->urlManager->createHostUrl('signin',true).'")' : 'signin("'.app()->controller->authHostInfo.'","account/authenticate/loginform?returnUrl='.request()->getUrlReferrer().'");';
    }
    /**
     * Dnaymic signup script
     * @param type $offSite True when page is loaded outside Shopbay, such as facebook
     * @return type
     */
    public function getSignupScript($offSite=false)
    {
        return $offSite ? 'newwindowpage("'.Yii::app()->urlManager->createHostUrl('signup',true).'")' : 'signup("'.app()->controller->authHostInfo.'");';
    }    
}