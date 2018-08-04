<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerApi');
Yii::import('common.modules.chatbots.providers.messenger.MessengerPayload');
Yii::import('common.modules.chatbots.providers.messenger.buttons.*');
Yii::import('common.modules.chatbots.providers.messenger.controllers.*');
Yii::import('common.modules.chatbots.providers.messenger.models.*');
Yii::import('common.modules.chatbots.providers.messenger.templates.*');
Yii::import('common.modules.chatbots.providers.messenger.threads.*');
Yii::import('common.modules.chatbots.providers.messenger.views.*');
/**
 * Description of MessengerTrait
 * 
 * @author kwlok
 */
trait MessengerTrait 
{
    /**
     * The facebook page access token
     * @var type 
     */
    protected $token;
    /**
     * Constructor.
     * @param type $token The facebook page access token
     */
    public function __construct($token) 
    {
        $this->token = $token;
    }
    /**
     * Send a text message
     * @param type $recipient
     * @param type $text
     * @param type $metadata
     */
    protected function sendTextMessage($recipient,$text,$metadata=null)
    {
        return $this->send(new TextMessage($recipient,$text,$metadata));
    }
    /**
     * Send an image
     * @param type $recipient
     * @param type $url Attachment url
     */
    protected function sendImage($recipient,$url)
    {
        return $this->sendAttachment($recipient,'image',$url);
    }
    /**
     * Send an audio
     * @param type $recipient
     * @param type $url Attachment url
     */
    protected function sendAudio($recipient,$url)
    {
        return $this->sendAttachment($recipient,'audio',$url);
    }
    /**
     * Send a video
     * @param type $recipient
     * @param type $url Attachment url
     */
    protected function sendVideo($recipient,$url)
    {
        return $this->sendAttachment($recipient,'video',$url);
    }
    /**
     * Send a file
     * @param type $recipient
     * @param type $url Attachment url
     */
    protected function sendFile($recipient,$url)
    {
        return $this->sendAttachment($recipient,'file',$url);
    }
    /**
     * Send an attachment
     * @param type $recipient
     * @param type $type Media type: either 'image', 'audio', 'video' or 'file'
     * @param type $url Attachment url
     */
    protected function sendAttachment($recipient,$type,$url)
    {
        return $this->send(new MessengerAttachment($recipient,$type,$url));
    }
    /**
     * Send a button template
     * <pre>
     * $recipient = 'recipient id';
     * $text = 'This is test button text';
     * $buttons = [];
     * $buttons[] = new WebUrlButton('Open Web URL','https://www.shopbay.org/');
     * $buttons[] = new PostbackButton('Help', MessengerPayload::HELP);
     * $buttons[] = new PhoneNumberButton('Call Phone Number', '+6512345678');//test number
     * </pre>
     * @param type $recipient
     * @param type $text
     * @param array $buttons
     */
    protected function sendButtonTemplate($recipient,$text,$buttons)
    {
        return $this->send(new ButtonTemplate($recipient,$text, $buttons));
    }
    /**
     * Send a Structured Message (Generic Template)
     * <pre>
     * $bubbles = [];//maximum 10
     * $buttons1 = [];//maximum 3
     * $buttons1[] = new WebUrlButton('Open Web URL','https://www.oculus.com/en-us/rift/');
     * $buttons1[] = new PostbackButton('Trigger Postback', 'USER_DEFINED_PAYLOAD_BUBBLE1');
     * $buttons1[] = new PhoneNumberButton('Call Phone Number', '+6512345678');//test number
     * $bubbles[] = new Bubble('rift', 'Next-generation virtual reality', 'https://www.oculus.com/en-us/rift/', 'https://www.shopbay.org/assets/fc419335/rift.png', $buttons1);
     * $buttons2 = [];
     * $buttons2[] = new WebUrlButton('Open Web URL','https://www.oculus.com/en-us/touch/');
     * $buttons2[] = new PostbackButton('Trigger Postback', 'USER_DEFINED_PAYLOAD_BUBBLE2');
     * $buttons2[] = new PhoneNumberButton('Call Phone Number', '+6512345678');//test number
     * $bubbles[] = new Bubble('touch', 'Your Hands, Now in VR', 'https://www.oculus.com/en-us/touch/', 'https://www.shopbay.org/assets/fc419335/touch.png', $buttons2);
     * </pre>
     * @param type $recipient
     * @param array $bubbles
     */
    protected function sendGenericTemplate($recipient,$bubbles)
    {
        return $this->send(new GenericTemplate($recipient,$bubbles));
    }
    /**
     * Send a Structured Message (Receipt Template)
     * @param type $recipient
     * @param Order $order
     */
    protected function sendReceiptTemplate($recipient, Order $order)
    {
        return $this->send(new ReceiptTemplate($recipient,$order));
    }
    /**
     * Send a message with Quick Replies (buttons).
     * <pre>
     * $recipient = 'recipient id';
     * $text = 'What\'s your favorite color?';
     * $replies = [];
     * $replies[] = new QuickReply('Red','RED');
     * $replies[] = new QuickReply('Green', 'GREEN');
     * $replies[] = new QuickReply('Blue', 'BLUE');
     * </pre>
     * @param type $recipient
     * @param type $text
     * @param QuickReply $replies
     */
    protected function sendQuickReplies($recipient,$text, $replies)
    {
        return $this->send(new QuickReplies($recipient,$text,$replies));
    }
    /**
     * Send a message with the account linking call-to-action
     */
    protected function sendAccountLinkingTemplate($recipient,$text, $url)
    {
        return $this->send(new AccountLinkingTemplate($recipient,$text,$url));
    }      
    /**
     * Send a message with the account unlinking call-to-action
     */
    protected function sendAccountUnlinkingTemplate($recipient,$text)
    {
        return $this->send(new AccountUnlinkingTemplate($recipient,$text));
    }      
    /**
     * Send a read receipt to indicate the message has been read
     * @param type $recipient
     */
    protected function sendReadReceipt($recipient)
    {
        return $this->send(new ReadReceipt($recipient));
    }    
    /**
     * Turn typing indicator on
     * @param type $recipient
     */
    protected function sendTypingOn($recipient)
    {
        return $this->send(new TypingOn($recipient));
    }
    /**
     * Turn typing indicator off
     * @param type $recipient
     */
    protected function sendTypingOff($recipient)
    {
        return $this->send(new TypingOff($recipient));
    }    
    /**
     * Send persistent menu
     */
    public function sendPersistenMenu($menus)
    {
        return $this->send(new PersistentMenu($menus));
    }    
    /**
     * Send greeting text
     */
    public function sendGreetingText($text)
    {
        return $this->send(new GreetingText($text));
    }    
    /**
     * Send get started button
     */
    public function sendGetStartedButton($payload)
    {
        return $this->send(new GetStartedButton($payload));
    }    
    /**
     * Send a message using the MessengerApi.
     * @see MessengerApi
     */
    protected function send($message)
    {
        return $this->api->send($message);
    }    
    /**
     * Get user profile information
     * @param string $userId the page-scoped user id
     * @param array $fields The user profile fields to query; If empty, means get all fields
     *              Fields are: 'first_name','last_name','profile_pic','locale','timezone','gender'
     * @return null| MessengerUserProfile
     */
    protected function getUserProfile($userId,$fields=[])
    {
        $data = $this->api->userProfile($userId,$fields);
        if ($data!=false){
            return new MessengerUserProfile($data);
        }
        else
            return null;
    }    
    /**
     * Get user page scoped id 
     * @param string $accountLinkingToken 
     * @return null|array
     */
    protected function getPSID($accountLinkingToken)
    {
        $data = $this->api->getPSID($accountLinkingToken);
        if ($data!=false){
            return $data;
        }
        else
            return null;
    }      
    /**
     * Get MessengerApi
     * @see MessengerApi
     */
    protected function getApi()
    {
        return new MessengerApi($this->token);
    }    
}
