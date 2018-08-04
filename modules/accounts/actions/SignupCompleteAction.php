<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.SignupCustomerForm');
/**
 * Description of SignupCompleteAction
 * This is called when user had successfully completed registration
 * 
 * @author kwlok
 */
class SignupCompleteAction extends CAction
{
    public $accountType = 'Account';
    public $emailCondition = [];
    
    public function run()
    {
        if (!isset($_GET['email']))
            throwError404(Sii::t ('sii', 'Email not found'));
        
        $this->controller->setPageTitle(Sii::t('sii','Thanks for registration'));
        
        $email = $_GET['email'];
        
        $account = $this->findSignupAccount($email);
        if ($account!=null)
            $this->controller->render('complete',['email'=>$email]);
        else
            $this->controller->render('complete',['activated'=>true]);
       
        Yii::app()->end();
    }
    
    protected function findSignupAccount($email)
    {
        $accountClass = $this->accountType;
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['email'=>$email]);
        if (!empty($this->emailCondition)){
            $criteria->addColumnCondition($this->emailCondition);
        }
        $account = $accountClass::model()->find($criteria);
        if ($account!=null && $account->pendingActivation())
            return $account;
        else
            return null;
    }    
}
