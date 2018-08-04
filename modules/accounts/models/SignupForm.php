<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.components.validators.PasswordValidator');
/**
 * SignupForm 
 *
 * @author kwlok
 */
class SignupForm extends SFormModel
{
    public $email;
    public $name;
    public $password;
    public $confirmPassword;
    public $verify_code;
    public $acceptTOS;
    public $accept;//currently checkbox not used
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
            array('name', 'length', 'max'=>32),
            array('password', 'length', 'max'=>64),
            array('password', 'PasswordValidator', 'strength'=>PasswordValidator::WEAK),
            array('name', 'length', 'min'=>6),

            array('email', 'email'),
            array('email', 'length', 'max'=>100),
            array('email, name', 'unique','className'=>'Account'),
            
            //for FullForm scenario - user has to fill up sign up form
            array('confirmPassword', 'required','on'=>'FullForm'),
            array('confirmPassword', 'compare','compareAttribute'=>'password','operator'=>'=','message'=>Sii::t('sii','Confirm password must be same as Password'),'on'=>'FullForm'),
            // verifyCode needs to be entered correctly
            array('verify_code', 'captcha', 'captchaAction'=>'accounts/signup/captcha', 'allowEmpty'=>!CCaptcha::checkRequirements(),'on'=>'FullForm'),
            
            //uncomment below if to enable accept checkbox
            //array('accept', 'boolean'),
            //array('accept', 'compare','compareValue'=>'1','operator'=>'=','message'=>Sii::t('sii','You have not accepted the User Agreement')),
        );
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'name' => Sii::t('sii','Username'),
            'password' => Sii::t('sii','Password'),
            'confirmPassword' => Sii::t('sii','Confirm Password'),
            'email' => Sii::t('sii','Email Address'),
            'verify_code'=>Sii::t('sii','Verification Code'),
            'accept'=>Sii::t('sii','I have read and accepted Terms of Service'),
            'acceptTOS'=>Sii::t('sii','By signing up, you agree to the {agreement}',array('{agreement}'=>CHtml::link(Sii::t('sii','Terms of Service'),url('terms')))),
        );
    }

    public function displayName() 
    {
        return Sii::t('sii','Signup Form');
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
}
