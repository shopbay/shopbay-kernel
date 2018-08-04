<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * MessageEvent represents the parameter for the {@link NotificationManager::onMessage onMessage} event.
 *
 * @author kwlok 
 */
class MessageEvent extends CEvent
{
    /**
     * @var string message recipient (single or multiple by role)
     */
    public $recipient;
    /**
     * @var string name of recipient (single or multiple by role)
     */
    public $recipientName;
    /**
     * @var string message recipients by role (multiple)
     */
    public $role;
    /**
     * @var string message subject
     */
    public $subject;
    /**
     * @var string message content
     */
    public $content;
    /**
     * Constructor.
     * @param string $recipient 
     * @param string $subject 
     * @param string $content 
     */
    public function __construct($recipient,$subject,$content)
    {
        $this->recipient=$recipient;
        $this->subject=$subject;
        $this->content=$content;
        parent::__construct(new Dispatcher());
    }
}