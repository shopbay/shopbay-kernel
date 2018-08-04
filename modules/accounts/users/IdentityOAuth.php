<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of IdentityOAuth
 *
 * @author kwlok
 */
class IdentityOAuth extends Identity 
{
    protected $_role = Role::USER;
    /**
     * Authenticates an oauth user. 
     * Password will not be verified as it is already done at Social Network sites
     * 
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
        else if ($usr->pendingSignup()){//this includes activation
            //for presignup account not yet activated, verify role customer
            if (!$this->hasRole($usr->id, [$this->getRole()]))
                $this->errorCode=self::ERROR_ROLE_INVALID;
            else {
                $this->setId($usr->id);
                $this->errorCode=self::ERROR_NONE;
            }
        }
        else if (!$usr->isActive()){
            $this->errorCode=self::ERROR_USER_INACTIVE;
        }
        else {
            //for activated account, verify role ACTIVATED only
            $this->authenticateRole($usr);
        }
        
        if ($this->errorCode!=self::ERROR_NONE)
            logError(__METHOD__.' error code '.$this->errorCode,[],false);
        
        return !$this->errorCode;
    }
}
