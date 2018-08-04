<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of Administrator
 *
 * @author kwlok
 */
class IdentityAdmin extends Identity
{
    protected $_role = Role::ADMINISTRATOR;
    /**
     * Authenticates a user.
     * @param boolean $hash Set if the Identity password is in clear or hashed; Default to "false"
     * @param boolean $activate Set to true if to indicate this is account activation request; Default to "false"
     * @return boolean whether authentication succeeds.
     */
    public function authenticate($hash=false,$activate=false)
    {
        $usr = $this->findAccountByName($this->name);
        
        if($usr===null){
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        }
        elseif ($hash && $this->password!=$usr->password){
           $this->errorCode=self::ERROR_PASSWORD_INVALID;
        }
        elseif (!$hash && !CPasswordHelper::verifyPassword($this->password, $usr->password)){
           $this->errorCode=self::ERROR_PASSWORD_INVALID;
        }
        else if ($usr->isSuspended()){
           $this->errorCode=self::ERROR_USER_SUSPENDED;
        }
        elseif ($usr->pendingActivation() && $usr->activate_time==null) {
            //password verification passed! gain access!
            //The activation controller will take over the process
            //@see ActivationController::actionIndex()
            logTrace(__METHOD__.' user is in pending activation',$usr->id);
            $this->setId($usr->id);
            $this->errorCode=self::ERROR_NONE;
        }
        else if (!$usr->isActive()){
            $this->errorCode=self::ERROR_USER_INACTIVE;
        }
        else if ($usr->id==Account::SUPERUSER){
            $this->setId($usr->id);
            $this->errorCode=self::ERROR_NONE;//after verifying password, superuser will gain access without checking role
        }
        else {
            $this->authenticateRole($usr,$this->getRole());
        }
        
        if ($this->errorCode!=self::ERROR_NONE)
            logError(__METHOD__.' error code '.$this->errorCode,[],false);
        
        return !$this->errorCode;
    }
}