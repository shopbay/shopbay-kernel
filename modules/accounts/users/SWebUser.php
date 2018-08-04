<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.AccountTypeTrait');
/**
 * Description of SWebUser
 * 
 * @author kwlok
 */
abstract class SWebUser extends RWebUser 
{
    use AccountTypeTrait;
    
    protected $_account;
    /**
     * @var string. The current user role; This normally is tied to application
     */
    public $currentRole;
    /**
     * @var boolean If to store avatar in session; Default to true
     */
    public $storeSessionAvatar = true;
    /**
     * @var boolean If to skip last login recording; Default to false
     */
    public $skipLastLoginRecording = false;
    
    public function afterLogin($fromCookie)
    {
        parent::afterLogin($fromCookie);
        
        if (!$this->isSuperuser && !$this->hasRole($this->currentRole)){
            //All accounts in shopbay have the default role 'USER' assigned when signup 
            //However, each individual shopbay-app might have additional role required to further restrict user access
            //e.g. shopbay-merchant accounts need to have role 'MERCHANT' to manage shop 
            //So, newly registered account with role 'USER is allowed to login to shopbay-merchant first but subject to access restriction by merchant app
            //Successful activated account will be assigned additonal role, e.g. MERCHANT
            logWarning(__METHOD__.' user has no role "'.$this->currentRole.'" but is accesing app '.Yii::app()->id);
            //--------------
            //TODO Comment off below to disallow cross app account login
            //logError(__METHOD__.' Unauthorized role access',[],false);
            //Yii::app()->user->logout();
            //throw new CException(Sii::t('sii','Unauthorized role access'));
            //--------------//
        }
        
        $this->account->last_login_ip = Yii::app()->getRequest()->getUserHostIPAddress();
        $this->account->last_login_time = time();
        if ($this->account->status == Process::PASSWORD_RESET){
            //password is reset to new one, if user successfully login, change back to active status
            $this->account->status = Process::ACTIVE;
        }

        if (!$this->skipLastLoginRecording && !$this->account->save()) {
            Yii::app()->user->logout();
            throw new CException(Sii::t('sii','Opps! Login Error. Please try again.'));
        }
        
        if (!is_null($this->account->profile))
            $this->setLocale($this->account->profile->locale);
        
        $this->setEmail($this->account->email);

        if ($this->storeSessionAvatar)
            $this->setAvatar($this->account->getAvatar(Image::VERSION_SMALL));        
    }    
    /*
     * Return account 
     */
    protected function getAccount()
    {
        if ($this->_account==null)
            $this->_account = $this->findAccountByPk($this->getId());
        return $this->_account;
    }
    /*
     * Return account profile
     */
    public function getAccountProfile()
    {
        return $this->getAccount()->profile;
    }  
    /**
     * @return boolean If user is authenticated (not a guest)
     */
    public function getIsAuthenticated()
    {
        return !$this->isGuest;
    }
    /**
     * @return boolean If user is a registered user (by default all registered user has role USER)
     * @see AccountManager::signup()
     * @see Account::signup()
     */
    public function getIsRegistered()
    {
        return $this->hasRole(Role::USER);
    }
    /**
     * @return boolean If user is activated (had clicked on the activation url link
     * @see AccountManager::activate()
     */
    public function getIsActivated()
    {
        return $this->hasRole(Role::ACTIVATED);
    }
    /**
     * @param $checkActivated If to undergo more stringent check - check if is activated
     * @return boolean If user is has role match with WebUser's current role (had already get assigned current role during signup) 
     * @see AccountManager::signup()
     * @see Account::signup()
     * @see ShopManager::startFirstShop()
     */
    public function getIsAuthorized($checkActivated=false)
    {
        $authorized = $this->hasRole($this->currentRole);
        if ($checkActivated)
            return $authorized && $this->isActivated;
        else
            return $authorized;
    }
    /**
     * @return boolean If user is both authorized and activated
     */
    public function getIsAuthorizedActivated()
    {
        return $this->getIsAuthorized(true);
    }
    /**
     * @return boolean If user is admin 
     */
    public function getIsAdmin()
    {
        return $this->hasRole(Role::ADMINISTRATOR);
    }
    /*
     * This function is meant to check functional tasks created via Rights.
     */
    public function hasRoleTask($task)
    {
        return $this->checkAccess($task);
    }
    /*
     * This function is meant to check functional role created via Rights.
     */
    public function hasRole($role)
    {
        return $this->checkAccess($role);
    }
    
    public function getRole()
    {
        return AuthAssignment::model()->getRoles($this->getId());
    }
    
    public function hasUnreadMessage()
    {
        return $this->getUnreadMessageCount()>0?true:false;
    }
    
    public function getUnreadMessageCount()
    {
        return Message::model()->mine()->unread()->count();
    }
    /**
     * Reset email when user has perform this action
     * @see ChangeEmailAction
     * @param string $email 
     */
    public function resetEmail($email)
    {
        $this->setEmail($email);
    }
    
    public function getEmail()
    {
        return $this->getState('_email');
    }
    
    public function setEmail($email)
    {
        $this->setState('_email',$email);
    } 
    
    public function getLocale()
    {
        return $this->getState('_locale',param('LOCALE_DEFAULT'));
    }
    
    public function setLocale($locale)
    {
        $this->setState('_locale',$locale,param('LOCALE_DEFAULT'));
    }   

    public function setAvatar($image)
    {
        $this->setState('_avatar',$image);
    }
    
    public function getAvatar($version=null)
    {
        if (isset($version))
            return $this->account->getAvatar($version); 
        else
            return $this->getState('_avatar');
    }

    public function hasApiAccessToken()
    {
        return $this->getApiAccessToken()!=null;
    }
    
    public function deleteApiAccessToken()
    {
        return Yii::app()->cache->delete('__api_access_token'.$this->getId());
    }
    
    public function getApiAccessToken()
    {
        return Yii::app()->cache->get('__api_access_token'.$this->getId());
    }
    
    public function setApiAccessToken($token)
    {
        Yii::app()->cache->set('__api_access_token'.$this->getId(), $token, 0);//never expire
    }      
}
