<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.components.validators.PasswordValidator');
/**
 * Description of PreSignupForm
 *
 * @author kwlok
 */
class PreSignupForm extends SignupForm
{
    public $network;
    public $token;//already base64_encoded
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
            // name, email, password are required
            array('email, name, password', 'required'),
            array('password', 'length', 'max'=>64),
            array('password', 'PasswordValidator', 'strength'=>PasswordValidator::WEAK),

            array('confirmPassword', 'required'),
            array('confirmPassword', 'compare','compareAttribute'=>'password','operator'=>'=','message'=>Sii::t('sii','Confirm password must be same as Password')),
            // verifyCode needs to be entered correctly
            array('verify_code', 'captcha', 'captchaAction'=>'accounts/signup/captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
            
            array('network, token', 'safe'),

        );
    }    
    /**
     * Set login id
     * Either username or email can be used as login id; Default to "email address"
     * But username for now kept as internal and follows email address
     */
    public function setLoginId($id)
    {
        $this->email = $id;
        $this->name = $this->email;
    }
    /**
     * Create form based on session user
     * @param SWebUser $user session user
     * @throws CException
     */
    public static function createForm($user)
    {
        $form = new PreSignupForm();
        $form->email = $user->email;
        Yii::import('common.modules.accounts.oauth.OAuth');
        foreach (OAuth::model()->findAccount($user->id) as $network) {
            $form->network = $network->provider;
            break;//only get the first network; during presignup, normally only one network account is created
        }
        $form->token = base64_encode($user->account->activate_str);
        return $form;
    }
}
