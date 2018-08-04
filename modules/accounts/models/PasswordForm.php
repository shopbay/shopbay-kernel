<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.components.validators.PasswordValidator');
Yii::import('common.modules.accounts.models.AccountTypeTrait');
/**
 * Description of PasswordForm
 *
 * @author kwlok
 */
class PasswordForm extends CFormModel
{
    use AccountTypeTrait;
    
    const POLICY_LENGTH_MAXIMUM = 15;
    const POLICY_LENGTH_MINIMUM = 8;
    
    public $currentPassword;
    public $newPassword;
    public $confirmPassword;
    public $verify_code;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            //all passwords are required
            ['currentPassword, newPassword, confirmPassword', 'required'],

            ['currentPassword', 'verifyPassword'],

            ['newPassword', 'length', 'max'=>self::POLICY_LENGTH_MAXIMUM],
            ['newPassword', 'length', 'min'=>self::POLICY_LENGTH_MINIMUM],
            ['newPassword', 'PasswordValidator', 'strength'=>PasswordValidator::WEAK],

            ['newPassword', 'compare','compareAttribute'=>'currentPassword','operator'=>'!=','message'=>Sii::t('sii','New Password cannot be the same as Current Password')],
            ['confirmPassword', 'compare','compareAttribute'=>'newPassword','operator'=>'=','message'=>Sii::t('sii','Confirm New Password must be the same as New Password')],
            // verifyCode needs to be entered correctly
            ['verify_code', 'required'],
            ['verify_code', 'captcha','captchaAction'=>'accounts/management/captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()],
        ];
    }
    /**
     *  Verify password
     */
    public function verifyPassword($attribute,$params)
    {
        $model = $this->findAccount();
        if (!CPasswordHelper::verifyPassword($this->currentPassword, $model->password))
            $this->addError('currentPassword',Sii::t('sii','Incorrect Current Password'));
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'currentPassword'=>Sii::t('sii','Current Password'),
            'newPassword'=>Sii::t('sii','New Password'),
            'confirmPassword'=>Sii::t('sii','Confirm New Password'),
            'verify_code'=>Sii::t('sii','Verification Code'),
        ];
    }

}