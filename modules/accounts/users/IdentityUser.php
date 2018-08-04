<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.accounts.users.Identity");
/**
 * IdentityUser represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 *
 * @author kwlok
 */
class IdentityUser extends Identity
{
    const ERROR_ACTIVATION_TOKEN  =20;
    const ERROR_ACTIVATION_EXPIRED=21;    
    const ERROR_ACTIVATION_NETWORK=22;    
    const ERROR_ACTIVATION_ACCOUNT=23;
    /**
     * if allow user to cross login other webapp which does not match his/her $currentRole
     * Even if cross app login is allowed, the access rights are still governed by authorization (rights)
     */
    protected $crossAppLogin = true;
    /*
     * Local property
     */
    protected $_role = Role::USER;
    /**
     * Authenticates a user.
     * @param boolean $hash Set if the Identity password is in clear or hashed; Default to "false"
     * @param boolean $activate Set to true if to indicate this is account activation request; Default to "false"
     * 
     * @return boolean whether authentication succeeds.
     */
    public function authenticate($hash=false,$activate=false)
    {
        $usr = $this->findAccountByName($this->name);
        
        if($usr===null) {
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        }
        elseif ($hash && $this->password!=$usr->password){
           $this->errorCode=self::ERROR_PASSWORD_INVALID;
        }
        elseif (!$hash && !CPasswordHelper::verifyPassword($this->password, $usr->password)){
           $this->errorCode=self::ERROR_PASSWORD_INVALID;
        }
        elseif ($usr->isSuspended()){
           $this->errorCode=self::ERROR_USER_SUSPENDED;
        }
        elseif ($usr->pendingActivation() && $usr->activate_time==null) {
            
            if (($activate || preg_match('/\baccount\/activate\b/', user()->returnUrl) ) &&
                CPasswordHelper::verifyPassword($this->password, $usr->password)){//a valid password is provided
                //password verification passed as per above steps! gain access!
                //The activation controller will take over the process
                //@see ActivationController::actionIndex()
                $this->afterAuthenticated($usr);
                logTrace(__METHOD__.' User is pending activation, and provides valid credentials.. passed!',request()->getRequestUri(),false);
            }
            else {
                logWarning(__METHOD__.' Non activated user attempt to non-authorized page.',[],false);
                $this->errorCode=self::ERROR_USER_INACTIVE;
            }
        }
        elseif (!$usr->isActive()){
            $this->errorCode=self::ERROR_USER_INACTIVE;
        }
        elseif ($usr->isActive() && $usr->activate_time!=null) {
            $this->authenticateRole($usr,$this->getRole());
        }

        if ($this->errorCode!=self::ERROR_NONE)
            logError(__METHOD__.' Error code '.$this->errorCode,[],false);
            
        return !$this->errorCode;
    }
    /**
     * @param type $errorCode
     * @return string error message
     */
    public static function errorMessage($errorCode=null)
    {
        switch ($errorCode) {
            case IdentityUser::ERROR_ACTIVATION_TOKEN:
                return Sii::t('sii','Invalid Token');
            case IdentityUser::ERROR_ACTIVATION_EXPIRED:
                return Sii::t('sii','Account activation period is already expired.');
            case IdentityUser::ERROR_ACTIVATION_NETWORK:
                return Sii::t('sii','Invalid Network');
            case IdentityUser::ERROR_ACTIVATION_ACCOUNT:
                return Sii::t('sii','Invalid Account');
            default:
                return Identity::errorMessage($errorCode);
        }
    }

}