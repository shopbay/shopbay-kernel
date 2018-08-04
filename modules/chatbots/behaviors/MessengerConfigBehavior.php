<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.Chatbot');
/**
 * Description of MessengerConfigBehavior
 *
 * @author kwlok
 */
class MessengerConfigBehavior extends CActiveRecordBehavior 
{
    /**
     * Get the last sent timestamp of field update
     * @param type $field
     * @return type
     */
    public function getLastSentTime($field,$text=false)
    {
        $timestamp = $this->getOwner()->getSetting($field.'LastSent');
        if (!$text)
            return $timestamp;
        
        if ($timestamp!=null)
            return Sii::t('sii','Last sent at {last_sent_time}',['{last_sent_time}'=>date('Y-m-d H:i', $timestamp)]);
        else
            return null;
    }    
    /**
     * Check if chatbot is verified by provider
     * @return boolean
     */
    public function getIsVerified()
    {
        return $this->getOwner()->getSetting('verified')!=null;
    }  
    /**
     * Save chatbot as verified (verified by provider)
     * @return boolean
     */
    public function saveAsVerified()
    {
        $this->getOwner()->saveSettings([
            'verified'=>true,
        ]);
    }      
    /**
     * Check if messenger plugin - message us - is enabled
     * @return boolean
     */
    public function getIsPluginMessageUsEnabled()
    {
        return $this->getOwner()->getSetting('messageUsPlugin')==true;
    }  
    /**
     * Check if messenger plugin - send to messenger - is enabled
     * @return boolean
     */
    public function getIsPluginSendToMessengerEnabled()
    {
        return $this->getOwner()->getSetting('sendToMessengerPlugin')==true;
    }  
    /**
     * Get the app id of facebook app used for messenger
     * @return string
     */
    public function getMessengerAppId()
    {
        return $this->getOwner()->getSetting('appId');
    }  
    /**
     * Get the facebook page id
     * @return string
     */
    public function getMessengerPageId()
    {
        return $this->getOwner()->getSetting('pageId');
    }  
    
    public function getIsSupportEnabled()
    {
        return $this->getOwner()->getSetting('support')==true;
    }  

    public function getSupportAgentId()
    {
        return trim($this->getOwner()->getSetting('agentId'));
    }  

    public function getSupportAgentName()
    {
        return trim($this->getOwner()->getSetting('agentName'));
    }  
    
    public function getSupportWorkingDays()
    {
        return $this->getOwner()->getSetting('workingDays');
    }  
    
    public function getSupportOpenTime()
    {
        return $this->getOwner()->getSetting('openTime');
    }  
    
    public function getSupportCloseTime()
    {
        return $this->getOwner()->getSetting('closeTime');
    }  
}
