<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Mailer class file.
 *
 * @author kwlok
 * @required PHPMailer v5.1
 *
 * A typical usage of Mailer is as follows:
 * <pre>
  $mail = new Mailer();
  $mail->send('testemail@gmail.com', 'Tester', 'Test Email Subject','<h1>Thanks for supporting {app}!</h1>');
  echo $mail->getError();
 *
 * -------------------------------
 * PHPMailer internal usage:
 *
 * //$body             = $mail->getFile('contents.html');
 * //$body             = eregi_replace("[\]",'',$body);
 * $body             = '<h1>Hello Mail!</h1>';
 * $mail->IsSMTP();
 * $mail->SMTPAuth   = true;// enable SMTP authentication
 * $mail->Host       = 'ssl://box476.bluehost.com:465';
 * $mail->Username   = "<username>";
 * $mail->Password   = "<password>"; 
 *
 * $mail->From       = "support@myapp.com";
 * $mail->FromName   = "Myapp.com";
 * $mail->Subject    = "This is the subject";
 * $mail->AltBody    = "This is the body when user views in plain text format"; //Text Body
 * $mail->WordWrap   = 50; // set word wrap
 *
 * $mail->MsgHTML($body);
 *
 * $mail->AddReplyTo("support@myapp.com","Myapp.com");
 *
 * $mail->AddAttachment("/path/to/file.zip");             // attachment
 * $mail->AddAttachment("/path/to/image.jpg", "new.jpg"); // attachment
 *
 * $mail->AddAddress("testuser@gmail.com","First Last");
 *
 * $mail->IsHTML(true); // send as HTML
 *
 * if(!$mail->Send()) {
 *   echo "Mailer Error: " . $mail->ErrorInfo;
 * } else {
 *  echo "Message has been sent";
 * }
 * </pre>
 *
 */
Yii::import('common.vendors.phpmailer.*');
require_once('class.phpmailer.php');

class Mailer 
{
    private $_mailer;
    private $_error='NULL';

    public function __construct() 
    {
        $this->_mailer             = new PHPMailer();
	$this->_mailer->CharSet    = 'UTF-8';
        $this->_mailer->IsSMTP();
        $this->_mailer->SMTPAuth   = true;//enable SMTP authentication
        $this->_mailer->SMTPSecure = readConfig('email','smtp_secure');//either tls or ssl
        $this->_mailer->Host       = readConfig('email','smtp_host');//tls uses 587, ssl uses 465
        $this->_mailer->Port       = readConfig('email','smtp_port');
        $this->_mailer->Username   = SSecurityManager::decryptData(readConfig('email','smtp_username'));
        $this->_mailer->Password   = SSecurityManager::decryptData(readConfig('email','smtp_password'));
        $this->_mailer->WordWrap   = 50; // set word wrap
        $this->_mailer->AltBody    = Sii::t('sii','You need an email client supports html to view this message.');
        $this->_mailer->IsHTML(true); // send as HTML
    }

    public function send($toAddress, $toName, $subject, $htmlBody, $attachment=null,$sender=null) 
    {
        $this->_setSenderName($sender);
        $this->_mailer->AddAddress($toAddress,$toName);
        //$htmlBody             = $mail->getFile('contents.html');
        //$htmlBody             = eregi_replace("[\]",'',$htmlBody);
        $this->_mailer->Subject = $subject;
        $this->_mailer->MsgHTML($htmlBody);
        if (isset($attachment)){
            $this->_mailer->AddAttachment($attachment);//attachment, e.g. /path/to/file.zip
            //$this->_mailer->AddAttachment("/path/to/image.jpg", "new.jpg"); // attachment
        }
        if(!$this->_mailer->Send()) {
            $this->_error = $this->_mailer->ErrorInfo;
            Yii::log(__METHOD__.' error info = '.$this->_error, CLogger::LEVEL_ERROR);
            return false;
        } 
        else
        {
            $this->_error = Process::OK;
            return true;
        }
    }

    public function getError()
    {
        return $this->_error;
    }
    
    public function hasError()
    {
        return !($this->_error==Process::OK);
    }

    private function _setSenderName($sender=null)
    {
        if (!isset($sender)){
            $sender = readConfig('email','sender_name');
        }
        
        //Reply to email should match the email address of outgoing mail server
        $replyTo = $this->_mailer->Username;
        $this->_mailer->SetFrom($replyTo, $sender);
        $this->_mailer->AddReplyTo($replyTo,$sender);
    }
}