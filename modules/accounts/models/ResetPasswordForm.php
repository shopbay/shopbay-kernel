<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.AccountTypeTrait');
/**
 * Description of ResetPasswordForm
 *
 * @author kwlok
 */
class ResetPasswordForm extends CFormModel
{
    use AccountTypeTrait;
    
    public $email;
    public $verify_code;
    
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            ['email, verify_code', 'required'],
            ['email', 'email'],
            ['email', 'length', 'max'=>100],
            ['email', 'verifycredential'],
            // verifyCode needs to be entered correctly
            ['verify_code', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()],
        ];
    }
    /**
     * Verify credential
     */
    public function verifyCredential($attribute,$params)
    {
        if (!$this->isAccountExists($this->email))
            $this->addError('email',Sii::t('sii','Email address not found. Please try again.'));
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'email'=>Sii::t('sii','Email Address'),
            'verify_code'=>Sii::t('sii','Verification Code'),
        ];
    }
}
