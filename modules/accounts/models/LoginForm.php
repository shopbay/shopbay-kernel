<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping user login form data. 
 *
 * @author kwlok
 */
class LoginForm extends CFormModel
{
    public $title;
    public $username;
    public $password;
    public $rememberMe;
    public $rememberMeDuration;
    public $acceptTOS;
    public $token;//activation token
    /**
     * Init
     */
    public function init()
    {
        if (!isset($this->rememberMeDuration))
            $this->rememberMeDuration = 3600*24*30;//default 30 days
    }        
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            // username and password are required
            ['username, password', 'required'],
            // rememberMe needs to be a boolean
            ['rememberMe', 'boolean'],
            ['token', 'safe'],
        ];
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'username'=>Sii::t('sii','Email Address'),
            'password'=>Sii::t('sii','Password'),
            'rememberMe'=>Sii::t('sii','Remember me'),
            'acceptTOS'=>Sii::t('sii','By signing in, you agree to the {agreement}',['{agreement}'=>CHtml::link(Sii::t('sii','Terms of Service'),url('terms'))]),
        ];
    }
    /**
     * Capture the activation token from url
     * @param type $url
     */
    public function setActivationToken($url)
    {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        $this->token = $query['token'];
    }
    
    public function getIsActivateMode()
    {
        return !empty($this->token);
    }
    /**
     * Setup login form
     * This also try to decide how to memorize session data depends on $rememberMe
     * @see CWebUser::login() $duration
     */
    public function setup($attributes)
    {
        $this->attributes = $attributes;
        if ($this->rememberMe==1)//rememberMe checkbox checked
            $this->rememberMeDuration = 3600*24*30;//default 30 days
        else    
            $this->rememberMeDuration = 3600*24*1;//default 1 day
    }
}