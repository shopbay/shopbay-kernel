<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.events.MessengerEvent');
/**
 * Postback Event
 *
 * This event is called when a postback is tapped on a Structured Message. 
 * https://developers.facebook.com/docs/messenger-platform/webhook-reference/postback-received
 *
 * @author kwlok
 */
class PostbackEvent extends MessengerEvent
{
    /**
     * The 'payload' param is a developer-defined field which is set in a postback button for Structured Messages. 
     * @var string The payload
     */
    public $payload;
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
        $this->payload = $data['payload'];
    }
}