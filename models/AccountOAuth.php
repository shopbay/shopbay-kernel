<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of AccountOAuth
 * This model acts as proxy to model Account but incorporating AccountProfile attribute access
 * 
 * @author kwlok
 */
class AccountOAuth extends Account 
{
    public $isNewUser = false;//first time sign in using social network account
    public $username;
    public $firstname;
    public $lastname;
    public $gender;
    public $birthday;
    /**
     * @return string username
     */
    public function getUsername()
    {
        return $this->name;
    }
    /**
     * Prepare signup form data
     * @return \OAuthSignupForm
     */
    public function prepareSignupForm()
    {
        $form = new OAuthSignupForm();
        //random generated passowrd, keep length max at 15; @see PasswordValidator rules
        //user supposed to set password when complete signup process
        $form->password = substr(Config::getSystemSetting('default_password').'@'.rand(1,1000), 0, 15);
        $form->email = $this->email;
        $form->name = $this->username;
        if (strlen($form->name)>32)//reach maximum name length 32
            $form->name = Helper::rightTrim($form->name,28);
        
        $form->profile = array(
            'first_name' => $this->firstname,
            'last_name' => $this->lastname,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'locale' => param('LOCALE_DEFAULT'),
        );
        
        return $form;
    }
    /**
     * Clone account (copy all attributes)
     * @param Account $account
     */
    public function cloneAccount($account)
    {
        if ($account instanceof Account){
            $this->id = $account->id;
            $this->attributes = $account->attributes;
        }
    }
}
