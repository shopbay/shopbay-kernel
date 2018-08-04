<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.components.validators.PasswordValidator');
/**
 * Description of SimplePasswordForm
 * Only take in new password, and do not verify current password
 * For certain user, can take in email field also
 *
 * @author kwlok
 */
class SimplePasswordForm extends PasswordForm
{
    public $email;
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            //skip verifying currentPassword
            ['newPassword, confirmPassword', 'required'],
            ['newPassword', 'length', 'max'=>self::POLICY_LENGTH_MAXIMUM],
            ['newPassword', 'length', 'min'=>self::POLICY_LENGTH_MINIMUM],
            ['newPassword', 'PasswordValidator', 'strength'=>PasswordValidator::WEAK],
            ['confirmPassword', 'compare','compareAttribute'=>'newPassword','operator'=>'=','message'=>Sii::t('sii','Confirm New Password must be the same as New Password')],

            ['email', 'required','on'=>'superuser'],
            ['email', 'email','on'=>'superuser'],
            ['email', 'length', 'max'=>100,'on'=>'superuser'],
            ['email', 'unique','className'=>'Account','on'=>'superuser'],

            // verifyCode needs to be entered correctly
            ['verify_code', 'required'],
            ['verify_code', 'captcha','captchaAction'=>'accounts/management/captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()],
        ];
    }
}