<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.events.MessengerEvent');
/**
 * Message Event
 *
 * This event is called when a message is sent to facebook page. The 'message' 
 * object format can vary depending on the kind of message that was received.
 * Read more at https://developers.facebook.com/docs/messenger-platform/webhook-reference/message-received
 * 
 * @author kwlok
 */
class MessageEvent extends MessengerEvent
{
    /**
     * @var array The message id
     */
    public $messageId;
    /**
     * @var array The app id
     */
    public $appId;
    /**
     * @var array The message text
     */
    public $text;
     /**
     * @var array The message metadata
     */
    public $metadata;
   /**
     * @var boolean If this is an echo message
     */
    public $isEcho;
    /**
     * @var boolean If this is a text message
     */
    public $isText;
    /**
     * @var boolean If this is a message containing attachments
     */
    public $isAttachments;
    /**
     * @var boolean If this is a quick reply
     */
    public $isQuickReply;
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
        $this->messageId = $data['mid'];
        $this->metadata = $this->parseStringData('metadata');
        $this->text = $this->parseStringData('text');
        $this->appId = $this->parseStringData('app_id');
        //Either get a text or attachment but not both
        $this->isText = $this->parseBooleanData('text');
        $this->isAttachments = $this->parseBooleanData('attachments');
        $this->isEcho = $this->parseBooleanData('is_echo');
        $this->isQuickReply = $this->parseBooleanData('quick_reply');
    }
    /**
     * @return null|string quick reply payload
     */
    public function getQuickReplyPayload()
    {
        if ($this->isQuickReply)
            return $this->data['quick_reply']['payload'];
        else 
            return null;
    }
    /**
     * @return array quick reply payload
     */
    public function getAttachments()
    {
        if ($this->isAttachments)
            return $this->data['attachments'];
        else 
            return [];
    }
}