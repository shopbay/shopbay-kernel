<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.customers.models.CustomerAccount');
/**
 * Description of AccountObjectBehavior
 * 
 * @author kwlok
 */
class AccountObjectBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of the attribute to store the account id. Defaults to 'account_id'
    */
    public $accountAttribute = 'account_id';
    /**
     * Get the proper account type model 
     * @return Account|CustomerAccount|MechantAccount
     */
    public function getAccount()
    {
        $account = Account::getAccountClass($this->getOwner()->{$this->accountAttribute});
        return $account::model()->findByAttributes(['id'=>$account!='Account'?Account::decodeId($this->getOwner()->{$this->accountAttribute}):$this->getOwner()->{$this->accountAttribute}]);
    }    
}