<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * GroupEmailEvent represents the parameter for the {@link NotificationManager::onGroupEmail onGroupEmail} event.
 *
 * @author kwlok 
 */
class GroupEmailEvent extends CEvent
{
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
     * @param string $role 
     * @param string $subject 
     * @param string $content 
     */
    public function __construct($role,$subject,$content)
    {
        $this->role=$role;
        $this->subject=$subject;
        $this->content=$content;
        parent::__construct(new Dispatcher());
    }
}