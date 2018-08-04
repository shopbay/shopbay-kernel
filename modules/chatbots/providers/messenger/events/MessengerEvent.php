<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.components.ChatbotEvent');
/**
 * MessengerEvent represents the parameter for the {@link MessengerCallbackHandler} event.
 *
 * @author kwlok
 */
class MessengerEvent extends ChatbotEvent
{
    /**
     * @var string The facebook page id
     */
    public $page;
    /**
     * @var string The recipient
     */
    public $recipient;
    /**
     * @var int The time of event
     */
    public $timestamp;
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
        $this->page = $page;
        $this->recipient = $recipient;
        $this->timestamp = $timestamp;
        parent::__construct($chatbot,$sender,$data);
    }
    /**
     * @see CEvent::$params
     * @return the event data
     */
    public function getData()
    {
        return $this->params;
    }
    /**
     * Parse string data field
     */
    protected function parseStringData($field)
    {
        return isset($this->data[$field])?$this->data[$field]:'undefined';
    }
    /**
     * Parse boolean data field
     */
    protected function parseBooleanData($field)
    {
        return isset($this->data[$field])?true:false;
    }    
    /**
     * Get class name
     * @return type
     */
    protected function getClassName()
    {
        return get_class($this);
    }
    /**
     * Return time stamp (epoch time in milliseconds) => 13 digits
     * @return sting $timestamp in format
     * 
     */
    public function getTimestampString()
    {
        return date('Y/m/d H:i:s',$this->timestamp/1000);
    }       
}