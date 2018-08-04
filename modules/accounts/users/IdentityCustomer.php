<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.accounts.users.IdentityUser");
Yii::import("common.modules.customers.models.CustomerAccount");
/**
 * IdentityCustomer represents the data needed to identity a shop customer.
 * It contains the authentication method that checks if the provided data can identity the user.
 *
 * @author kwlok
 */
class IdentityCustomer extends IdentityUser
{
    /*
     * Local property
     */
    public $shop;
    /**
     * Constructor.
     * @param string $cid The unique customer id (@see static::createCid() for how it is obtained)
     * @param string $password password
     */
    public function __construct($cid,$password)
    {
        $data = static::parseCid($cid);
        $this->shop = $data['shop'];
        $this->setAccountType('CustomerAccount');
        $this->setAccountPk(['shop_id'=>$this->shop,'email'=>$this->getName()]);
        $this->setEmailCondition(['shop_id'=>$this->shop]);
        parent::__construct($data['username'], $password);
    }
    /**
     * Authenticate user role
     * User minimially must have Role::ACTIVATED 
     * 
     * @param CustomerAccount $acct
     * @param string $role User role
     * @throws CException
     */
    protected function authenticateRole($acct,$role=null)
    {
        $qualifiedRoles = $this->_defaultRoles;
        if (isset($role))
            $qualifiedRoles = array_merge($qualifiedRoles,[$role]);
        
        if ($this->crossAppLogin){
            if (!$this->hasRole(Account::encodeId(Account::TYPE_CUSTOMER,$acct->id), $this->_defaultRoles))//check default roles only
                $this->errorCode=self::ERROR_ROLE_INVALID;
            else
                $this->afterAuthenticated($acct);
        }
        else {
            if (!$this->hasRole(Account::encodeId(Account::TYPE_CUSTOMER,$acct->id), $qualifiedRoles))
                $this->errorCode=self::ERROR_ROLE_INVALID;
            else
                $this->afterAuthenticated($acct);
        }
    }    
    /**
     * Assign identity id after role is authenticated
     * @param type $acct
     */
    protected function afterAuthenticated($acct)
    {
        $this->setId(Account::encodeId(Account::TYPE_CUSTOMER,$acct->id));
        $this->errorCode=self::ERROR_NONE;
    }
    /**
     * Create IdentityCustomer id (cid) that contains both shop and username information.
     * The cid value is encoded with following format:
     * shop/username
     * 
     * cid will be used as the param $username to construct IdentityCustomer instance
     * 
     * @param integer $shop
     * @param string $username
     * @return string
     */
    public static function createCid($shop,$username)
    {
        return $shop.'/'.$username;
    }
    /**
     * Parse cid to return both shop and customer username 
     * 
     * @param string $cid
     * @return array 
     * [ 
     *   'shop'=>'<shop_id>',
     *   'username'=>'<username>',
     * ]
     */
    public static function parseCid($cid)
    {
        $data = [];
        $array = explode('/', $cid);
        if (isset($array[0]))
            $data['shop'] = $array[0];//position index 0 is shop id
        if (isset($array[1]))
            $data['username'] = $array[1];//position index 1 is username
        //Make sure both mandatory data keys are present in $data
        foreach (['shop','username'] as $key) {
            if (!array_key_exists($key, $data)){
                logError(__METHOD__." cid = $cid",$data);
                throw new CException('Invalid cid array');
            }
        }
        return $data;
    }        
}