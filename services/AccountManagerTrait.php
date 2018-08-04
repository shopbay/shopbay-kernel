<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AccountManagerTrait
 *
 * @author kwlok
 */
trait AccountManagerTrait 
{
    /**
     * Authenticate account
     * 
     * @param IUserIdentity $identity the user identity
     * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
     * @param boolean $audit Set if to record login activity
     * @param boolean $hash Set if the Identity password is in clear or hashed; Default to "false"
     * @param boolean $returnInfo Set to true if to return back account info [id,name]; Default to "false"
     * @param boolean $activate Set to true if to indicate this is account activation request; Default to "false"
     * @param boolean $oauthByToken Set to true if to indicate this is account authenticated via oauth token; Default to "false"
     * @throws CException
     */
    public function authenticate($identity,$duration=0,$audit=true,$hash=false,$returnInfo=false,$activate=false,$oauthByToken=false)
    {
        if (!($identity instanceof IdentityUser || $identity instanceof IdentityAdmin|| $identity instanceof IdentityOAuth))
            throw new CException(Sii::t('sii','Invalid Identity'));
        
        $identity->authenticate($hash,$activate);
        switch ($identity->errorCode) {
            case Identity::ERROR_USERNAME_INVALID:
                logError(__METHOD__.' invalid username',[],false);
                throw new CException(Identity::errorMessage(Identity::ERROR_USERNAME_INVALID));
            case Identity::ERROR_USER_SUSPENDED:
                logError(__METHOD__.' suspsended username',[],false);
                throw new CException(Identity::errorMessage(Identity::ERROR_USER_SUSPENDED));
            case Identity::ERROR_PASSWORD_INVALID:
                logError(__METHOD__.' invalid password',[],false);
                throw new CException(Identity::errorMessage(Identity::ERROR_PASSWORD_INVALID));
            case Identity::ERROR_ROLE_INVALID:
                logError(__METHOD__.' invalid role',[],false);
                throw new CException(Identity::errorMessage(Identity::ERROR_ROLE_INVALID));
            case Identity::ERROR_USER_INACTIVE:
                logError(__METHOD__.' inactive account',[],false);
                throw new CException(Identity::errorMessage(Identity::ERROR_USER_INACTIVE));
            case Identity::ERROR_NONE:
                logTrace(__METHOD__.' Yii::app()->user = '.get_class(Yii::app()->user),get_class($identity));
                Yii::app()->user->currentRole = $identity->getRole();//align user role
                if ($identity->accountType=='CustomerAccount'){//sub account
                    Yii::app()->user->setAccountType($identity->accountType);  
                    Yii::app()->user->setAccountPk(['shop_id'=>$identity->shop,'email'=>$identity->getName()]);  
                }
                /**
                 * for oauth mode no need to recording last login info - authentication already done?
                 * This also avoid some weird sQL error that cannot update account via Api token
                 * SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction
                 */
                Yii::app()->user->skipLastLoginRecording = $oauthByToken;
                Yii::app()->user->login($identity,$duration);                
                if ($audit){
                    $this->execute(Yii::app()->user->account, [
                        'recordActivity'=> $this->getRecordActivity(Activity::EVENT_LOGIN),
                    ]);                
                }
                logInfo(__METHOD__.' ok');
                if ($returnInfo){
                    return [
                        'id'=>Yii::app()->user->getId(),
                        'username'=>$identity->getName(),
                    ];
                }
                else
                    return true;
            default:
                logError(__METHOD__.' undefined error code',true,false);
                throw new CException(Identity::errorMessage(Identity::ERROR_UNKNOWN_IDENTITY));
        }
    }      
    /**
     * Change account password
     * 
     * @param integer $user Session user id
     * @param CFormModel $form PasswordForm
     * @return CModel $model
     * @throws CException
     */
    public function changePassword($user,$form)
    {
        if (!($form instanceof PasswordForm))
            throw new CException(Sii::t('sii','Invalid form'));
        
        if(!$form->validate()){
            logError(__METHOD__.' validation error',$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }
        
        $model = $this->findAccount();
        if ($model===null)
            throw new CException(Sii::t('sii','Account not found'));
        $model->password = CPasswordHelper::hashPassword($form->confirmPassword);
        
        $this->validate($user, $model, false);
        
        return $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>[
                'event'=>Activity::EVENT_CHANGE_PASSWORD,
                'account'=>$user,
                'description'=>Sii::t('sii','Change Password'),
            ],
        ]);
    }      
    /**
     * Change account email
     * 
     * @param integer $user Session user id
     * @param CFormModel $form EmailForm
     * @return CModel $model
     * @throws CException
     */
    public function changeEmail($user,$form)
    {
        if (!($form instanceof EmailForm))
            throw new CException(Sii::t('sii','Invalid form'));
        
        if(!$form->validate()){
            logError(__METHOD__.' Validation error',$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }
        
        $model = $this->findAccount();
        if ($model===null)
            throw new CException(Sii::t('sii','Account not found'));

        $this->validate($user, $model, false);
       
        return $this->execute($model, [
                'changeEmail'=>['email'=>$form->email,'userid'=>$user],
                'recordActivity'=>[
                    'event'=>Activity::EVENT_CHANGE_EMAIL,
                    'account'=>$user,
                    'description'=>$form->email,
                ],
                self::NOTIFICATION=>self::EMPTY_PARAMS,
        ]);
    }      
    /**
     * Reset account password
     * 
     * @param integer $user Session user id
     * @param CFormModel $form ResetPasswordForm
     * @param array $emailCondition The extra email criteria; Applicable for shop customer account, or merchant staff account
     * @return CModel $model
     * @throws CException
     */
    public function resetPassword($user,$form,$emailCondition=[])
    {
        if (!($form instanceof ResetPasswordForm))
            throw new CException(Sii::t('sii','Invalid form'));
        
        if(!$form->validate()){
            logError(__METHOD__.' validation error',$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }
        
        $model = $this->findAccountByEmail($form->email,$emailCondition);
        if ($model===null)
            throw new CException(Sii::t('sii','Account not found'));
        if (!$model->isActive())
            throw new CException(Sii::t('sii','Inactive Account'));

        $this->validate($user, $model, false);

        $newPassword = $this->randomPassword();
        $model->password = CPasswordHelper::hashPassword($newPassword);
        $model->status = Process::PASSWORD_RESET;
        if($model->save()){
            $model->password = $newPassword;
            $this->getNotificationManager()->send($model);
        }
        else
            throw new CException(Sii::t('sii','Failed to reset password'));
    }       
    /**
     * A wrapper method to find account
     * @return type
     */
    protected function findAccount()
    {
        return Account::model()->mine()->find();
    }
    /**
     * A wrapper method to find account
     * @return type
     */
    protected function findAccountByEmail($email,$emailCondition=[])
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['email'=>$email]);
        if (!empty($emailCondition)){
            $criteria->addColumnCondition($emailCondition);
        }
        return Account::model()->find($criteria);
    }
    /**
     * A wrapper method to find account
     * @return type
     */
    protected function findAccountByToken($token,$extraCriteria=[])
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['activate_str'=>base64_decode($token)]);
        if (!empty($extraCriteria)){
            $criteria->addColumnCondition($extraCriteria);
        }
        return Account::model()->find($criteria);
    }
    /**
     * Validate activation token
     * @param type $token
     * @param array $extraCriteria column "field"=>"value"
     * @return type
     * @throws CException
     */
    public function validateActivationToken($token,$extraCriteria=[]) 
    {
        $acct = $this->findAccountByToken($token,$extraCriteria);
        if ($acct==null) {
            logError(__METHOD__.' Account token not found '.$token);
            $this->logout(true);//force user logut for any activation error
            throw new CException(IdentityUser::errorMessage(IdentityUser::ERROR_ACTIVATION_TOKEN),IdentityUser::ERROR_ACTIVATION_TOKEN);
        }
        
        if ($acct->isActivationExpired){
            logError(__METHOD__.' Account activation period is already expired.');
            $acct->status = Process::SUSPEND;
            $acct->update();
            $this->logout(true);//force user logut for any activation error
            throw new CException(IdentityUser::errorMessage(IdentityUser::ERROR_ACTIVATION_EXPIRED),IdentityUser::ERROR_ACTIVATION_EXPIRED);
        }
        
        return $acct;
    }
    /**
     * This method is used internally to create account after successful activation validation
     * @param Account|CustomerAccount $acct
     * @param string $password If not null, will reset password to this
     * @param Identity $identity If not null, will use this identity for authentication
     * @return boolean
     * @throws CException
     */
    protected function activateAccountInternal($acct,$password=null,$identity=null)
    {
        $acct->activate_str = Role::ACTIVATED.' '.Process::OK;//this also invalidate the activate_str
        $acct->activate_time = time();
        if (isset($password))
            $acct->password = CPasswordHelper::hashPassword($password);
        
        if(!$acct->validate()){
            logError(__METHOD__.' Account validation error',$acct->getErrors(),false);
            $this->logout(true);//force user logout for any activation error
            throw new CException(IdentityUser::errorMessage(IdentityUser::ERROR_ACTIVATION_TOKEN),IdentityUser::ERROR_ACTIVATION_TOKEN);
        }

        $transaction = Yii::app()->db->beginTransaction();
        
        try {

            $acct->status = Process::ACTIVE;//default status after activation
            $acct->update();
            $acct->refresh();
            logInfo(__METHOD__.' User status is now '.$acct->status);

            $this->assignRole(Role::ACTIVATED, $acct);
            logInfo(__METHOD__.' User with role '.Role::ACTIVATED.' assigned.');

            //Perform auto login
            if (!isset($identity))
                $identity = $this->parseIdentity($acct->name,$acct->password);
            
            if($identity->__authenticate__()){
                $this->execute($acct, [
                    'recordActivity'=> $this->getRecordActivity(Activity::EVENT_ACCOUNT_ACTIVATE),
                ]);
                
                if ($identity instanceof IdentityAdmin){
                    if (!$acct->isSuperuser)//superuser already did once during first time sign in, no need to go through another round for forced password change.
                        $acct->status = Process::ACCOUNT_NEW_PASSWORD;//changed to password reset to force changing password upon first login
                    $acct->update();
                    $acct->refresh();
                    logInfo(__METHOD__.' User[IdentityAdmin] status is now changed to '.$acct->status);
                }
                elseif ($identity instanceof IdentityMerchant){//Assign Role Merchant to IdentityMerchant only
                    Rights::assign(Role::MERCHANT, $acct->id);
                    logInfo(__METHOD__.' User[IdentityMerchant] with role '.Role::MERCHANT.' assigned.');
                }
                
                if ($identity instanceof IdentityUser){//Only send notification for IdentityUser, no for IdentityAdmin
                    //Here uses Yii::app()->user->account (which still has status SIGNUP) to get the welcome notification sent out
                    $this->execute(Yii::app()->user->account, [
                        self::NOTIFICATION=>self::EMPTY_PARAMS,
                    ]);
                }
                
                Yii::app()->user->account->status = $acct->status;//synchronize the status; SWebUser account is stored in memory
                Yii::app()->user->login($identity);
                $transaction->commit();
                logInfo(__METHOD__.' ok');
                return true;
            }
            else {
                logError(__METHOD__.' Auto authentication failed - errorCode='.$identity->errorCode);
                Yii::app()->user->logout();//force logout
                throw new CException(Identity::errorMessage($identity->errorCode),$identity->errorCode);
            }


        } catch (CException $e) {
            logError(__METHOD__.' Rollback: '.$e->getMessage().' >> '.$e->getTraceAsString(),[],false);
            $transaction->rollback();
            Yii::app()->user->logout();//force logout
            throw new CException($e->getMessage(),$e->getCode());
        }
    }
    /**
     * A wrapper method to assign role
     * @param type $role
     * @param type $account
     */    
    protected function assignRole($role,$account) 
    {
        Rights::assign($role, $account->id);
    }
    /**
     * A quick method to determine which identity class to be used
     * @param $username
     * @param $password
     * @return IdentityAdmin|IdentityUser
     * @throws CException
     */
    protected function parseIdentity($username,$password)
    {
        if (app()->user instanceof WebAdmin)
            return new IdentityAdmin($username,$password);
        elseif (Yii::app()->user instanceof MerchantUser)
            return new IdentityMerchant($username,$password);
        elseif (Yii::app()->user instanceof CustomerUser){
            if (Yii::app()->user->onShopScope()){
                $cid = IdentityCustomer::createCid(Yii::app()->user->getShop(), $username);
                return new IdentityCustomer($cid,$password);
            }
            else
                return new IdentityUser($username,$password);
        }
        else
            throw new CException(Sii::t('sii','Undefined Web User'));
    }
    
    protected function getRecordActivity($event)
    {
        $activity = [
            'event'=>$event,
            'description'=>$this->getActivityDescription(),
            'account'=>Yii::app()->user->getId(),
        ];
        if ($this->runMode=='api')//under api mode, API server may not have user media files, hence use static icon
            $activity = array_merge($activity,[
                'icon_url'=>'<i class="fa fa-user fa-fw"></i>',
            ]);
        return $activity;
    }
        
    protected function getActivityDescription()
    {
        return 'user_name='.Yii::app()->user->account->name.
               ', site='.Yii::app()->id.
               ', ip='.Yii::app()->getRequest()->getUserHostIPAddress().
               ', user_agent='.Yii::app()->getRequest()->getUserAgent();      
    }     
    
    protected function randomPassword()
    {
        //pwd contains "~" char fails to hashed password comparison. Hence have to replace it with any char, here use "1".
        $pwd = Yii::app()->securityManager->generateRandomString(8);
        $result = strtr($pwd, '~', '1');
        return $result;
    }
   
}
