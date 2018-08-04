<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.customers.models.CustomerAccount');
/**
 * Description of AccountBehavior
 * Requires Account model, and user()->getId()
 * 
 * @author kwlok
 */
class AccountBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of account source. Defaults to null
    */
    public $accountSource;
    /**
    * @var string The name of the attribute to store the account id. Defaults to 'account_id'
    */
    public $accountAttribute = 'account_id';
    /**
    * @var string The name of the attribute to do sorting (order). Defaults to 'create_time'
    */
    public $sortAttribute = 'create_time';
    /**
    * @var boolean Indicate if to include Global settings.  Defaults to 'false'
    */
    public $includeGlobal = false;

    public function mine($account=null) 
    {
        if (!isset($account))
            $account = $this->parseUserId();
        
        $criteria = new CDbCriteria();
        $criteria->addCondition($this->accountAttribute.'=\''.$account.'\'');//to support both acc and customer acc
        if ($this->includeGlobal)
            $criteria->addInCondition($this->accountAttribute, array(Account::SYSTEM,user()->getId()?user()->getId():Account::GUEST),'OR');
        //logTrace(__METHOD__,$criteria);
        $this->getOwner()->getDbCriteria()->mergeWith($criteria);
        return $this->getOwner();
    }
    
    public function notMine($tableAlias=null)
    {
        if (isset($tableAlias))
            $this->accountAttribute = $tableAlias.'.'.$this->accountAttribute;

        $user = $this->parseUserId();
        
        $criteria = new CDbCriteria();
        $criteria->addCondition($this->accountAttribute.'!=\''.$user.'\'');
        if ($this->includeGlobal)
            $criteria->addNotInCondition($this->accountAttribute, array(Account::SYSTEM,$user),'OR');
        //logTrace(__METHOD__,$criteria);
        $this->getOwner()->getDbCriteria()->mergeWith($criteria);
        return $this->getOwner();
    }
    public function updatable($user=null)
    {
        return $this->getAccountOwner()->{$this->accountAttribute}==(isset($user)?$user:$this->parseUserId(false));
    }

    public function deletable($user=null)
    {
        return $this->getAccountOwner()->{$this->accountAttribute}==(isset($user)?$user:$this->parseUserId(false));
    }        

    public function recently($limit=-1) 
    {
        $this->getOwner()->getDbCriteria()->mergeWith(array(
                'order'=>$this->sortAttribute.' DESC',
                'limit'=>$limit,
                'offset'=>0,
            ));
        return $this->getOwner();
    }

    public function offset($value=-1) 
    {
        $this->getOwner()->getDbCriteria()->mergeWith(array(
                'offset'=>$value,
            ));
        return $this->getOwner();
    }        
    /**
     * Return the account owner; Default to "$this->getOwner()"
     * 
     * @return string account attribute
     */
    public function getAccountOwner()
    {
        if (isset($this->accountSource)){
            return $this->getOwner()->{$this->accountSource};
        }
        else 
           return $this->getOwner();
    }    
    /**
     * Set the account owner
     */
    public function setAccountOwner($owner)
    {
        $this->accountSource = $owner;
    }    
    /**
     * Set the account attribute
     */
    public function setAccountAttribute($attribute)
    {
        $this->accountAttribute = $attribute;
    }    
    /**
     * Return the account attribute
     * @return string account attribute
     */
    public function getAccountAttribute()
    {
        return $this->accountAttribute;
    }    
    /**
     * Auto detect which user id to use depends on account type
     * @param type $returnGuest If to return as guest if user()->getId() is not available
     */
    protected function parseUserId($returnGuest=true)
    {
        $user = user()->getId()?user()->getId():($returnGuest?Account::GUEST:null);
        
        //convert $account for sub account type
        if ($this->getOwner() instanceof SActiveRecord && Account::isSubAccount($this->getOwner()))
            $user = Account::decodeId($user);
        
        return $user;
    }
}