<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.users.SWebUser');
Yii::import('common.modules.plans.components.ShopSubscriptionTrait');
/**
 * Description of WebUser
 *
 * @author kwlok
 */
class WebUser extends SWebUser 
{
    use ShopSubscriptionTrait;
    /**
     * @var boolean True to support cross-subdomain authentication
     */
    public $enableCookieSharing = false;
    /**
     * Private property to support keyPrefix
     * @see WebUser::getStateKeyPrefix()
     */
    private $_keyPrefix;
    private $_keyPrefix_className = 'WebUser';
    private $_keyPrefix_appId = 'SHOPBAY';
    public  $menu;
    
    public function init()
    {
        parent::init();
        $this->menu = new CMap();
        if ($this->enableCookieSharing){
            $this->identityCookie = cookieSharingSettings();
        }
    }

    public function afterLogin($fromCookie)
    {
        parent::afterLogin($fromCookie);

        if ($this->account->profile!=null){
            $this->setNickname($this->account->nickname);
        }
    }
    
    public function getNickname()
    {
        return $this->getState('_nickname');
    }
    
    public function setNickname($nickname)
    {
        return $this->setState('_nickname',$nickname);
    }
    /**
     * This is set to be the same so that to support cross subdomain authentication
     * 
     * @return string a prefix for the name of the session variables storing user session data.
     */
    public function getStateKeyPrefix()
    {
        if ($this->enableCookieSharing){
            if($this->_keyPrefix==null){
                $this->_keyPrefix = md5('Yii.'.$this->_keyPrefix_className.'.'.$this->_keyPrefix_appId);
            }   
            return $this->_keyPrefix;
        }
        else
            return parent::getStateKeyPrefix();
    }
}