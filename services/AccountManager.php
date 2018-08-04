<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.services.AccountManagerTrait");
/**
 * Description of AccountManager
 *
 * @author kwlok
 */
class AccountManager extends ServiceManager
{
    use AccountManagerTrait;
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }
    /**
     * Create new account
     * Administrator will set username, email (and initial role sufficient for activation)
     * System will generate random password and send out invitation, but pending last thing to be accomplished by users:
     * [1] Activation (also verifying email)
     * [2] Change password
     *
     * @param integer $adminUser the account who creates this user
     * @param CFormModel $form UserForm
     * @return CModel $model
     * @throws CException
     */
    public function create($adminUser,$form)
    {
        if (!($form instanceof UserForm))
            throw new CException(Sii::t('sii','Invalid form'));

        if ($adminUser!=Account::SUPERUSER)//for now restrict to only superuser can create user
            throw new CException(Sii::t('sii','Invalid account creator'));

        //next do validation
        if(!$form->validate()){
            logError(__METHOD__.' validation error for scenario='.$form->getScenario(),$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }

        $model = new Account();
        $model->attributes = $form->attributes;
        if (empty($model->password))
            $pwd = $this->randomPassword();
        else
            $pwd = $model->password;
        $model->password = CPasswordHelper::hashPassword($pwd);
        $model->reg_ip = request()->getUserHostAddress();
        $model->setActivationToken($model->email);
        $model->status = Process::ACCOUNT_NEW;

        $model = $this->execute($model, [
            'create'=>[Role::USER,Role::ADMINISTRATOR],//default assign two roles
        ]);

        //user back original password for notification sending
        $model->password = $pwd;
        $model = $this->execute($model, [
            'recordActivity'=> [
                'event'=>Activity::EVENT_CREATE,
                'account'=>$adminUser,
            ],
            self::NOTIFICATION=>$model,
        ]);

        logInfo(__METHOD__.' ok');

        return $model;
    }
    /**
     * Update account profile 
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);

        if ($model->account->address->hasAddress()){
            if (!$model->account->address->validate()){
                logError(__METHOD__.' validation error',$model->account->address->getErrors(),false);
                throw new CException(Sii::t('sii','Validation Error'));
            }
        }
        return $this->execute($model, [
            'saveAccountAddress'=>self::EMPTY_PARAMS,
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>[
                'event'=>Activity::EVENT_UPDATE,
                'account'=>$user,
            ],
        ]);
    }  
    /**
     * Sign up account
     * Use case: When user first time using the app, they will have to signup account by providing email and password
     * System will sign up an local account but pending last thing to be accomplished by users:
     * [1] Activation (also verifying email)
     * 
     * @param CFormModel $form SignupForm
     * @return CModel $model
     * @throws CException
     */
    public function signup($form)
    {
        if (!$form instanceof SignupForm)
            throw new CException(Sii::t('sii','Invalid form'));
        
        //set login id to be identical to email address
        $form->setLoginId($form->email);
        
        //next do validation
        if(!$form->validate()){
            logError(__METHOD__.' validation error for scenario='.$form->getScenario(),$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }

        $model = new Account();
        $model->setLoginId($form->email);
        $model->password = CPasswordHelper::hashPassword($form->password);
        $model->reg_ip = request()->getUserHostIPAddress();
        $model->setActivationToken($model->email);
        $model->status = Process::SIGNUP;

        $this->validate(Account::GUEST, $model, false);

        $account = $this->execute($model, [
            'signup'=>self::EMPTY_PARAMS,
            self::NOTIFICATION=>self::EMPTY_PARAMS,
        ]);
        
        if ($form instanceof SignupCustomerForm && $form->address->hasAddress()){
            $account->profile->mobile = $form->mobile;
            $account->profile->alias_name = $form->alias_name;
            logTrace(__METHOD__.' saving account profile information...',$account->profile->attributes);
            $account->profile->update();
            $account->address = new AccountAddress();
            $account->address->account_id = $account->id;
            $account->address->attributes = $form->address->attributes;
            logTrace(__METHOD__.' saving account address information...',$account->address->attributes);
            $account->address->save();
            logInfo(__METHOD__.' account profile and address saved ok');
        }
        
        logInfo(__METHOD__.' ok');
        
        return $account;
    }   
    /**
     * Pre-Sign up account
     * Use case: When user login using oauth (any social network account) for the first time,
     * System will pre-sign up an local account but pending two things to be accomplished by users:
     * [1] Activation (also verifying email)
     * [2] Setting new password (local to app)
     * 
     * System will send also notification containing activation link and signup form for user to complete the process
     * (above notification is done separately via external controller)
     * 
     * @param CFormModel $form SignupForm
     * @return CModel $model
     * @throws CException
     */
    public function presignup($form)
    {
        if (!($form instanceof OAuthSignupForm))
            throw new CException(Sii::t('sii','Invalid form'));
        
        //next do validation
        if(!$form->validate()){
            logError(__METHOD__.' validation error for scenario='.$form->getScenario(),$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }

        $model = new Account();
        $model->setLoginId($form->email);
        $model->password = CPasswordHelper::hashPassword($form->password);
        $model->reg_ip = request()->getUserHostIPAddress();
        $model->setActivationToken($model->email);
        $model->status = Process::PRESIGNUP;

        $this->validate(Account::GUEST, $model, false);

        return $this->execute($model, [
            'signup'=>$form->profile,
        ]);
    }    
    /**
     * Activate pre-sign up account
     * 
     * @param CFormModel $form SignupForm
     * @return CModel $model
     * @throws CException
     */
    public function activatePresignup($form)
    {
        if (!($form instanceof PreSignupForm))
            throw new CException(Sii::t('sii','Invalid form'));
        
        $acct = Account::model()->findByAttributes([
            'email'=>$form->email,
            'status'=>Process::PRESIGNUP,
        ]);
        if ($acct==null) {
            logError(__METHOD__.' Account email not found, or status!='.Process::PRESIGNUP,$form->attributes);
            $this->logout(true);//force user logut for any activation error
            throw new CException(IdentityUser::errorMessage(IdentityUser::ERROR_ACTIVATION_ACCOUNT),IdentityUser::ERROR_ACTIVATION_ACCOUNT);
        }
        
        $email = $this->activate($form->token, true, $form->network);
        $form->setLoginId($email);//always set login id equals to email
        
        //next do validation
        if(!$form->validate()){
            logError(__METHOD__.' validation error for scenario='.$form->getScenario(),$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }

        return $this->activateAccountInternal($acct,$form->password);
    }       
    /**
     * Resend activation email
     * 
     * @param integer $user Session user id
     * @param CFormModel $form SignupForm
     * @return CModel $model
     * @throws CException
     */
    public function resendActivationEmail($email)
    {
        $form = new EmailForm();
        $form->email = trim($email);
        $form->setScenario('resendActivation');
        if(!$form->validate(['email'])){
            logError(__METHOD__.' validation error for scenario='.$form->getScenario(),$form->getErrors(),false);
            throw new CException(Sii::t('sii','Invalid email address'));
        }
        
        $model = Account::model()->findByAttributes(['email'=>$form->email]);
        if ($model===null)
            throw new CException(Sii::t('sii','Account not found'));

        if ($model->pendingActivation()){
            $model->setActivationToken($model->email);
            return $this->execute($model, [
                'update'=>self::EMPTY_PARAMS,
                self::NOTIFICATION=>self::EMPTY_PARAMS,
            ]);
        }
        else
            throw new CException(Sii::t('sii','Invalid Account'));
    }      
    /**
     * Login account
     * 
     * @param CFormModel $form LoginForm
     * @return boolean True for successful login
     * @throws CException
     */
    public function login($form)
    {
        if (!($form instanceof LoginForm))
            throw new CException(Sii::t('sii','Invalid form'));
        
        if(!$form->validate()){
            logError(__METHOD__.' Login form validation error',$form->getErrors(),false);
            throw new CException(Sii::t('sii','Validation Error'));
        }
        return $this->authenticate($this->parseIdentity($form->username, $form->password),$form->rememberMeDuration);
    }     
    /**
     * Logout account
     * 
     * @param boolean $force if is forced logout due to any error / reasons; In this case, activity is not logged
     */
    public function logout($force=false)
    {
        if (!Yii::app()->user->isGuest){
            if (!$force){
                $this->execute(Yii::app()->user->account, [
                    'recordActivity'=> $this->getRecordActivity(Activity::EVENT_LOGOUT),
                ]);                
            }
            Yii::app()->user->logout();
            logInfo(__METHOD__.' '.($force?' forced logout due to error / reason':'ok')); 
        }
        else {
            Yii::app()->user->logout();
            logWarning(__METHOD__.' User logout without session <- could be due to logout at other subdomain..?'); 
        }
    }   
    /**
     * Activate account
     * 
     * @param $token activation token
     * @param $validate boolean if to validate account but no proceeding real activation
     * @param $network network name for validation
     * @return boolean True for successful activation
     * @throws CException
     */
    public function activate($token,$validate=false,$network=null)
    {
        $acct = $this->validateActivationToken($token);
        
        if (isset($network) && $validate){
            Yii::import('common.modules.accounts.oauth.widgets.OAuthNetworks');
            if (!in_array($network, OAuthNetworks::networks())){
                logError(__METHOD__.' network not found '.$network);
                $this->logout(true);//force user logut for any activation error
                throw new CException(IdentityUser::errorMessage(IdentityUser::ERROR_ACTIVATION_NETWORK),IdentityUser::ERROR_ACTIVATION_NETWORK);
            }
            Yii::import('common.modules.accounts.oauth.OAuth');
            if (OAuth::model()->findAccount($acct->id,$network)===null){
                logError(__METHOD__.' network '.$network.' not found in table '.OAuth::model()->tableName().' for user '.$acct->id);
                $this->logout(true);//force user logut for any activation error
                throw new CException(IdentityUser::errorMessage(IdentityUser::ERROR_ACTIVATION_NETWORK),IdentityUser::ERROR_ACTIVATION_NETWORK);
            }
        }
        
        //if run in validate mode, will not proceed below; return user email;
        if ($validate) {
            return $acct->email;
        }
        else {
            return $this->activateAccountInternal($acct);
        }
    }     
    /**
     * Suspend account  
     * 
     * @param integer $user Session user id
     * @param integer $account Account id to suspend
     * @return CModel $account
     * @throws CException
     */
    public function suspend($user,$account)
    {
        if ($user != Account::SUPERUSER)
            throw new CException(Sii::t('sii','Unauthorized Access'));
        
        $model = Account::model()->findByPk($account);
        if ($model===null)
            throw new CException(Sii::t('sii','Account not found'));

        $model->status = Process::SUSPEND;
        $this->validate($user, $model, false);

        return $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_ACCOUNT_SUSPEND,
                'account'=>$user,
            ),
        ]);
    }      
    /**
     * Resume account  
     * 
     * @param integer $user Session user id
     * @param integer $account Account id to resume
     * @return CModel $account
     * @throws CException
     */
    public function resume($user,$account)
    {
        if ($user != Account::SUPERUSER)
            throw new CException(Sii::t('sii','Unauthorized Access'));
        
        $model = Account::model()->findByPk($account);
        if ($model===null)
            throw new CException(Sii::t('sii','Account not found'));

        $model->status = Process::ACTIVE;
        $this->validate($user, $model, false);

        return $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>[
                'event'=>Activity::EVENT_ACCOUNT_RESUME,
                'account'=>$user,
            ],
        ]);
    }
    /**
     * First password reset (for newly created account and first time attempt)
     * @param $account
     * @param $form
     * @return CModel
     * @throws CException
     * @throws ServiceOperationException
     */
    public function firstPasswordReset($account,$form)
    {
        if (!$account->passwordChangeRequired()){
            throw new CException(Sii::t('sii','Invalid account status'));
        }
        
        $account = $this->changePassword($account->id, $form);
        //set email if any
        if (isset($form->email)){
            $account->email = $form->email;
        }

        if ($account->isSuperuser){
            Yii::app()->user->logout();//force logout
            return $this->execute($account, [
                'prepareEmailActivation'=>self::EMPTY_PARAMS,
                'recordActivity'=>[
                    'event'=>Activity::EVENT_CHANGE_EMAIL,
                ],
                self::NOTIFICATION=>self::EMPTY_PARAMS,
            ]);
        }
        else {
            $account->status = Process::ACTIVE;
            return $this->execute($account, [
                'update'=>self::EMPTY_PARAMS,
            ]);
        }

    }
    /**
     * Close an account
     * When an account is closed, following will be performed.
     * [1] Need to validate if account can be closed <- any outstanding payments
     * [2] set all shops' status to 'offline' / inactive if any (for merchant users)
     * [3] Set account status to 'closed'
     * [4] Change password to random value, rename username and email to something else that cannot be used (this will allowed email to be reused for new signed)
     * [5] Logs out the user
     *
     * @param CActiveRecord $account the account to be closed
     * @return CModel $model
     * @throws CException
     */
    public function close($account)
    {
        if (!$account instanceof Account)
            throw new CException(Sii::t('sii','Invalid account model'));

        $transaction = Yii::app()->db->beginTransaction();

        try {

            //scan through all subscriptions all must be with 'Charged' status or no overdue payment
            foreach (Subscription::model()->mine()->findAll() as $subscription){
                if (!$subscription->isCharged)
                    throw new CException(Sii::t('sii','This account cannot be closed. You have subscription found with unpaid status.'));
                if ($subscription->isPastdue)
                    throw new CException(Sii::t('sii','This account cannot be closed. You need to make payment for overdue subscription first.'));
            }

            //bring all active shops to offline
            foreach (Shop::model()->mine()->active()->findAll() as $shop){
                $this->shopManager->transition($account->id, $shop , 'deactivate');
                logInfo(__METHOD__.' shop '.$shop->id.' is brought to offline.');
            }

            $email = $account->email;
            $account->password = CPasswordHelper::hashPassword($this->randomPassword());
            $account->name = 'CLOSED_'.$account->name;//prefix a closed stamp
            if (strlen($account->name)>32)
                $account->name = substr($account->name, 0, 32);
            $account->email = 'CLOSED_'.$email;//prefix a closed stamp
            if (strlen($account->email)>100)
                $account->email = substr($account->email, 0, 100);
            $account->status = Process::ACCOUNT_CLOSED;

            $account = $this->execute($account, [
                'update'=>self::EMPTY_PARAMS,
                'recordActivity'=> [
                    'event'=>Activity::EVENT_CLOSE,
                ],
            ]);

            //user back original email for notification sending
            $account->email = $email;
            $account = $this->execute($account, [
                self::NOTIFICATION=>$account,
            ]);

            $transaction->commit();
            logInfo(__METHOD__.' account is closed.');

            Yii::app()->user->deleteApiAccessToken();
            Yii::app()->user->logout();
            logInfo(__METHOD__.' user logout.');

            logInfo(__METHOD__.' ok');

            return $account;

        } catch (CException $e) {
            logError(__METHOD__.' rollback: '.$e->getMessage().' >> '.$e->getTraceAsString(),[],false);
            $transaction->rollback();
            throw new CException($e->getMessage(),$e->getCode());
        }

    }
}
