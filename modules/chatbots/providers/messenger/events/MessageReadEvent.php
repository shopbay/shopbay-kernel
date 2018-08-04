<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.events.MessengerEvent');
/**
 * Message Read Event
 *
 * This event is called when a previously-sent message has been read.
 * https://developers.facebook.com/docs/messenger-platform/webhook-reference/message-read
 * 
 *
 * @author kwlok
 */
class MessageReadEvent extends MessengerEvent
{
    /**
     * @var string The watermark
     */
    public $watermark;
    /**
     * @var string The sequence number
     */
    public $sequenceNumber;
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
        $this->watermark = $data['watermark'];
        $this->sequenceNumber = $data['seq'];
    }
}