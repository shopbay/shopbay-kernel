<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AccountTypeTrait
 *
 * @author kwlok
 */
trait AccountTypeTrait 
{
    protected $accountType = 'Account';
    protected $accountPk;//for sub account type which has composite primary key such as CustomerAccount
    protected $emailCondition = [];
    
    public function setAccountType($modelClass) 
    {
        $this->accountType = $modelClass;
    }
    public function getAccountType() 
    {
        return $this->accountType;
    }
    /**
     * Optional; For sub account type which has composite primary key such as CustomerAccount $shop_id, $email
     * @param array $pk The primary key value
     */
    public function setAccountPk($pk) 
    {
        $this->accountPk = $pk;
    }
    /**
     * Optional; Needed for customer account type
     * @param array $condition extra criteria column condition on top of "email" field
     */
    public function setEmailCondition($condition) 
    {
        $this->emailCondition = $condition;
    }
    /**
     * Check if account is already exists
     * @param type $email
     * @return type
     */
    public function isAccountExists($email)
    {
        $accountClass = $this->accountType;
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['email'=>$email]);
        if (!empty($this->emailCondition)){
            $criteria->addColumnCondition($this->emailCondition);
        }
        return $accountClass::model()->exists($criteria);
    }
    /**
     * Find user account
     * @return type
     */
    public function findAccount()
    {
        $accountClass = $this->accountType;
        return $accountClass::model()->mine()->find();
    }
    /**
     * Find account by primary key
     * @param mixed $id The primary key value
     * @return type
     */
    public function findAccountByPk($id)
    {
        $accountClass = $this->accountType;
        if (Account::isSubType($id) && isset($this->accountPk)){
            logTrace(__METHOD__.' accountPk',$this->accountPk);
            return $accountClass::model()->findByPk($this->accountPk);
        }
        else
            return $accountClass::model()->findByPk($id);
    }
    /**
     * Find account by username
     * @param mixed $username The account user name
     * @return type
     */
    public function findAccountByName($username)
    {
        $accountClass = $this->accountType;
        if (!empty($this->emailCondition)){
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(['email'=>$username]);//email is the username
            $criteria->addColumnCondition($this->emailCondition);
            logTrace(__METHOD__.' $criteria',$criteria);
            return $accountClass::model()->find($criteria);
        }
        else
            return $accountClass::model()->find('name=:username or email=:username',[':username'=>$username]);
    }
    /**
     * Find active account by username
     */
    protected function findActiveAccountByName($username)
    {
        $accountClass = $this->accountType;
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['status'=>Process::ACTIVE]);
        if (!empty($this->emailCondition)){
            $criteria->addColumnCondition(['email'=>$username]);//email is the username
            $criteria->addColumnCondition($this->emailCondition);
        }
        else {
            $criteria->addColumnCondition(['name'=>$username]);
        }
        logTrace(__METHOD__.' $criteria',$criteria);
        return $accountClass::model()->find($criteria);
    }    
}
