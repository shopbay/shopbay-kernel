<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.events.MessengerEvent');
/**
 * Account Link Event
 *
 * This event is called when the Link Account or UnLink Account action has been tapped.
 * https://developers.facebook.com/docs/messenger-platform/webhook-reference/account-linking
 *
 * @author kwlok
 */
class AccountLinkEvent extends MessengerEvent
{
    /**
     * @var string The status
     */
    public $status;
    /**
     * @var string The authorization code
     */
    public $authCode;
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
        $this->status = $data['status'];
        if (isset($data['authorization_code']))
            $this->authCode = $data['authorization_code'];
    }
}