<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.services.notification.events.NotificationEvent');
/**
 * EmailEvent represents the parameter for the {@link NotificationManager::onEmail onEmail} event.
 *
 * @author kwlok
 */
class EmailEvent extends NotificationEvent
{
    /**
     * @var string email address to send to 
     */
    public $addressTo;
    /**
     * @var string name of the owner of email address
     */
    public $addressName;
    /**
     * @var string message subject
     */
    public $subject;
    /**
     * @var string message content
     */
    public $content;
    /**
     * @var string attachment path e.g. /path/to/attachment.zip
     */
    public $attachment;
    /**
     * @var string name of the sender of the email
     */
    public $senderName;
    /**
     * Constructor.
     * @param string $addressTo 
     * @param string $addressName 
     * @param string $subject 
     * @param string $content 
     * @param string $mode 
     * @param string $attachment 
     * @param string $senderName 
     */
    public function __construct($addressTo,$addressName,$subject,$content,$mode=null,$attachment=null,$senderName=null)
    {
        $this->addressTo=$addressTo;
        $this->addressName=$addressName;
        $this->subject=$subject;
        $this->content=$content;
        $this->mode=isset($mode)?$mode:Config::getSystemSetting('email_mode');
        $this->attachment=$attachment;
        $this->senderName=$senderName;
        parent::__construct(new Dispatcher());
    }
}