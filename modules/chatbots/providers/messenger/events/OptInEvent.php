<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.events.MessengerEvent');
/**
 * Authentication (Opt In) Event
 *
 * The value for 'optin.ref' is defined in the entry point. For the "Send to 
 * Messenger" plugin, it is the 'data-ref' field. Read more at 
 * https://developers.facebook.com/docs/messenger-platform/webhook-reference/authentication
 *
 * @author kwlok
 */
class OptInEvent extends MessengerEvent
{
    /**
     * The 'ref' field is set in the 'Send to Messenger' plugin, in the 'data-ref'
     * The developer can set this to an arbitrary value to associate the 
     * authentication callback with the 'Send to Messenger' click event. 
     * This is a way to do account linking when the user clicks the 'Send to Messenger' plugin.
     * @var string The pass through param
     */
    public $passThroughParam;
    /**
     * Constructor.
     * @param string $chatbot The chatbot
     * @param string $page The facebook page id
     * @param string $sender 
     * @param string $recipient 
     * @param int $timestamp 
     * @param array $data the event data
     */
    public function __construct($chatbot,$page,$sender,$recipient,$timestamp,$data)
    {
        parent::__construct($chatbot,$page,$sender,$recipient,$timestamp,$data);
        $this->passThroughParam = $data['ref'];
    }
}