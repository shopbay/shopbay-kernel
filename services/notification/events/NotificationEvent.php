<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of NotificationEvent.
 *
 * @author kwlok
 */
class NotificationEvent extends CEvent
{
    const SYNCHRONOUS = 'sync';
    const ASYNCHRONOUS = 'async';
    /**
     * @var string email mode; Default to "asynchronous" 
     */
    public $mode;
}