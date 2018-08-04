<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 *
 * @author kwlok
 */
class ContactForm extends SFormModel
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $verify_code;
    /**
     * Model display name 
     * @return string the model display name
     */
    public function displayName()
    {
        return Sii::t('sii','Contact Form');
    }     
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            // name, email, subject and body are required
            array('name, email, subject, body', 'required'),
            array('name, email, subject', 'length', 'max'=>100),
            array('body', 'length', 'max'=>5000),
            // email has to be a valid email address
            array('email', 'email'),
            array('body','rulePurify'),
            // verifyCode needs to be entered correctly
            array('verify_code', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'name'=>Sii::t('sii','Name'),
            'email'=>Sii::t('sii','Email'),
            'subject'=>Sii::t('sii','Subject'),
            'body'=>Sii::t('sii','Anything that you want to ask us'),
            'verify_code'=>Sii::t('sii','Verification Code'),
        );
    }

    //BELOW IS USED BY NOTIFICATION MANAGER
    
    public function getMailAddressTo()
    {
        return Config::getSystemSetting('email_contact');
    }
    
    public function getMailAddressName()
    {
        return readConfig('email','sender_name');
    }
    
    public function getMailSubject()
    {
        return Sii::t('sii','Customer feedback from {customer}',array('{customer}'=>$this->name));
    }
    
    public function getMailBody()
    {
        $body = '<p>'.Sii::t('sii','From').': '.$this->name.' <em>'.$this->email.'</em></p>';
        $body .= '<p>'.Sii::t('sii','Subject').': '.$this->subject.'</p>';
        $body .= '<p>'.Sii::t('sii','Content').': '.$this->body.'</p>';
        return $body;
    }
    
}