<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.Chatbot');
/**
 * Description of ChatbotConfigBehavior
 *
 * @author kwlok
 */
class ChatbotConfigBehavior extends CActiveRecordBehavior 
{
    private $_chatbot;//chatbot instance
    /**
     * Get the chatbot model
     * @return Chatbot
     */
    public function getChatbot($provider)
    {
        if (!isset($this->_chatbot))
            $this->_chatbot = Chatbot::model()->forOwner(get_class($this->getOwner()),$this->getOwner()->id,$provider)->find();
        return $this->_chatbot;
    }
    /**
     * Check if owner has chatbot
     * @return boolean
     */
    public function hasChatbot($provider)
    {
        return $this->getOwner()->getChatbot($provider)!=null;
    }
    /**
     * Check if chatbot is verified
     * @return boolean
     */
    public function isChatbotVerified($provider)
    {
        return $this->getOwner()->getChatbot($provider)->getIsVerified();
    }
    /**
     * Get messenger
     * @return Chatbot
     */
    public function getMessenger()
    {
        return $this->getOwner()->getChatbot(Chatbot::MESSENGER);
    }  
}
