<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.events.MessengerEvent');
/**
 * Delivery Confirmation Event
 *
 * This event is sent to confirm the delivery of a message. Read more about 
 * these fields at https://developers.facebook.com/docs/messenger-platform/webhook-reference/message-delivered
 *
 * @author kwlok
 */
class DeliveryEvent extends MessengerEvent
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
     * @var array The message ids
     */
    public $messageIds = [];
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
        if (isset($data['mids'])){
            $this->messageIds = $data['mids'];
        }
    }
}