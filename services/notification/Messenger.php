<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.components.ChatbotContext");
Yii::import("common.modules.chatbots.providers.messenger.MessengerPayload");
Yii::import("common.modules.chatbots.providers.messenger.views.*");
/**
 * Description of Messenger
 * 
 * @author kwlok
 */
class Messenger extends CComponent
{
    CONST TYPE_PAYLOAD = 'payload';
    /**
     * Send to Mesenger
     * @param type $sender The chatbot owner 
     * @param type $recipient
     * @param type $payload This field is mapped to {@link NotificationEvent} $subject field, but carry payload information
     * @param type $method This field is mapped to {@link NotificationEvent} $content, and is used to decide how to send Messeneg
     * @param type $params extra payload params
     */
    public function send($sender,$recipient,$payload,$method,$params=[])
    {
        $context = new ChatbotContext($this->getClientId($sender), 'unset', $recipient);
        if ($method==self::TYPE_PAYLOAD){//the qualifying condition
            $view = new $payload['view']($this->getPageAccessToken($sender));
            $view->setContext($context);
            return $view->render(new $payload['class']($payload['type'], $params));
        }
        else {
            logError(__METHOD__.' Unknown method.');
            return false;
        }
    }
    /**
     * Get facebook page access token
     * @return type
     */
    public function getPageAccessToken($sender)
    {
        return $sender->getClientAttribte('fbPageAccessToken');
    }
    /**
     * Get chatbot client id
     * @return type
     */
    public function getClientId($sender)
    {
        return $sender->getClientAttribte('fbBotClientId');
    }
}