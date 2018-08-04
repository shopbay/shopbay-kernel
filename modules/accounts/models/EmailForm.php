<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.AccountTypeTrait');
/**
 * Description of EmailForm
 *
 * @author kwlok
 */
class EmailForm extends CFormModel
{
    use AccountTypeTrait;
    
    public $password;
    public $email;
    public $cemail;
    public $verify_code;
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            ['password, email, cemail, verify_code', 'required'],
            ['email, cemail', 'email'],
            ['email', 'length', 'max'=>100],
            ['cemail', 'compare','compareAttribute'=>'email','operator'=>'=','message'=>Sii::t('sii','Confirm New Email must be same as New Email')],
            // verifyCode needs to be entered correctly

            ['verify_code', 'captcha','captchaAction'=>'accounts/management/captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()],
            ['password', 'verifyCredential'],
            ['email', 'ruleEmail'],//todo Should verify also shop_id!
        ];
    }
    /**
     * Email must be unique per shop
     * @param type $attribute
     * @param type $params
     * @throws CException
     */
    public function ruleEmail($attribute,$params)
    {
        if ($this->getScenario()=='resendActivation'){
            //@see AccountManager::resendActivationEmail()
            //For activation token resend, email must exists
            if (!$this->isAccountExists($this->email)){
                $this->addError('email', Sii::t('sii','Email Address "{email}" does not exists.',['{email}'=>$this->email]));
            }
        }
        else { //for normal account registration
            if ($this->isAccountExists($this->email)){
                $this->addError('email', Sii::t('sii','Email Address "{email}" has already been taken.',['{email}'=>$this->email]));
            }
        }
    }
    /**
     * Verify credential
     */
    public function verifyCredential($attribute,$params)
    {
        $model = $this->findAccount();
        if (!CPasswordHelper::verifyPassword($this->password, $model->password))
            $this->addError('password',Sii::t('sii','Invalid Password'));
        if ($this->email==$model->email)
            $this->addError('email',Sii::t('sii','New Email cannot be the same as current email {email}',['{email}'=>$model->email]));
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'password'=>Sii::t('sii','Password'),
            'email'=>Sii::t('sii','New Email'),
            'cemail'=>Sii::t('sii','Confirm New Email'),
            'verify_code'=>Sii::t('sii','Verification Code'),
        ];
    }

}