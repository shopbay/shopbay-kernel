<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.AccountTypeTrait');
/**
 * Description of Identity
 *
 * @author kwlok
 */
class Identity extends CUserIdentity 
{
    use AccountTypeTrait;
    
    const ERROR_ROLE_INVALID  = 10;
    const ERROR_USER_INACTIVE = 11;
    const ERROR_USER_SUSPENDED= 12;
    /**
     * if allow user to cross login other webapp which does not match his/her $currentRole
     * Even if cross app login is allowed, the access rights are still governed by authorization (rights)
     */
    protected $crossAppLogin = false;
    /*
     * Local property
     */
    protected $_id;
    protected $_role;
    protected $_defaultRoles = [Role::ACTIVATED];//user must have this role to be able to pass authentication
    /**
     * Authenticates an oauth user. 
     * @param boolean $hash Set if the Identity password is in clear or hashed; Default to "false"
     * @param boolean $activate Set to true if to indicate this is account activation request; Default to "false"
     * @return boolean whether authentication succeeds.
     */
    public function authenticate($hash=false,$activate=false)
    {
        throw new CException('Method not implemented');
    }
    /**
     * By default, CUserIdentity assumes the {@link username} is a unique identifier
     * and thus use it as the {@link id ID} of the identity.
     * Here id != username. Hence need to override getId()
     *
     * Note: $this->getName() returns the login id (either email or username) dependes on user key in
     */
    public function getId()
    {
        return $this->_id;
    }
    /**
     * @see above notes getId()
     */
    public function setId($id)
    {
        $this->_id = $id;
    }
    /**
     * Return the user role
     * @return type
     */
    public function getRole()
    {
        return $this->_role;
    }
    /**
     * Check if identity has role
     * @param type $id
     * @param array $roles All roles in array must be qualified
     * @return boolean
     */
    protected function hasRole($id,$roles=[])
    {
        $checkRole = function($role) use ($id) {
            $auth = AuthAssignment::model()->find('userid=:userid and itemname=\''.$role.'\'',
                array(':userid'=>$id));
            if ($auth==null){
                logWarning(__METHOD__.' User has no '.$role.' role.',[],false);//not to print out user credential in log
                return false;
            }
            else {
                return true;
            }
        };
        $hasRole = false;//default to true
        foreach ($roles as $index => $role){
            if ($index==0)
                $hasRole =  true;//make it to start with true always
            $hasRole = $hasRole && $checkRole($role);//any one role is false, the resultant is false
        }
        return $hasRole;
    }
    /**
     * Authenticate user role
     * User minimially must have Role::ACTIVATED 
     * 
     * @param Account $acct
     * @param string $role User role
     * @throws CException
     */
    protected function authenticateRole($acct,$role=null)
    {
        $qualifiedRoles = $this->_defaultRoles;
        if (isset($role))
            $qualifiedRoles = array_merge($qualifiedRoles,[$role]);
        
        if ($this->crossAppLogin){
            if (!$this->hasRole($acct->id, $this->_defaultRoles))//check default roles only
                $this->errorCode=self::ERROR_ROLE_INVALID;
            else
                $this->afterAuthenticated($acct);
        }
        else {
            if (!$this->hasRole($acct->id, $qualifiedRoles))
                $this->errorCode=self::ERROR_ROLE_INVALID;
            else
                $this->afterAuthenticated($acct);
        }
    }
    /**
     * Authenticates a user by auto verifying encrypted password stored in DB (user did not supply password)
     * Used by controller account/activate to perform auto login trigger by activation token,
     * Use case is user activation 
     * 
     * @return boolean whether authentication succeeds.
     */
    public function __authenticate__()
    {
        $acct = $this->findActiveAccountByName($this->name);
        if($acct===null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else if ($acct->password!==$this->password)
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else if ($acct->isSuspended()){
            $this->errorCode=self::ERROR_USER_SUSPENDED;
        }
        else {
            //for activated account, verify role ACTIVATED only
            $this->authenticateRole($acct);
        }
        if ($this->errorCode!=self::ERROR_NONE)
            logError(__METHOD__.' error code '.$this->errorCode,[],false);
        return !$this->errorCode;
    }
    /**
     * Assign identity id after role is authenticated
     * @param type $acct
     */
    protected function afterAuthenticated($acct)
    {
        $this->setId($acct->id);
        $this->errorCode=self::ERROR_NONE;
    }
    /**
     * @param type $errorCode
     * @return string error message
     */
    public static function errorMessage($errorCode=null)
    {
        switch ($errorCode) {
            case Identity::ERROR_USER_SUSPENDED:
                return Sii::t('sii','This account is suspended');
            case Identity::ERROR_USERNAME_INVALID:
            case Identity::ERROR_PASSWORD_INVALID:
                return Sii::t('sii','Wrong username or password');
            case Identity::ERROR_ROLE_INVALID:
                return Sii::t('sii','Invalid Role');
            case Identity::ERROR_USER_INACTIVE:
                return Sii::t('sii','Inactive Account. Please activate your account');
            case Identity::ERROR_NONE:
                return Sii::t('sii','Account OK');
            case Identity::ERROR_UNKNOWN_IDENTITY:
            default:
                return Sii::t('sii','Unknown Identity {code}',array('{code}'=>$errorCode));
        }
    }
    
}
