<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.Chatbot');
/**
 * Description of ChatbotContext
 * 
 * Chatbot context is required when a chatting session is established.
 * Minimally it contains session information such as:
 *   - who is the sender, 
 *   - which chatbot client is being used, 
 *   - and the corresponding chatbot provider app id
 * 
 * Mainly used for view procesing
 * 
 * @author kwlok
 */
class ChatbotContext extends CComponent 
{
    /**
     * The chatbot instance
     * @var Chatbot 
     */
    private $_bot;
    /**
     * The chatbot session 
     * @var string 
     */
    private $_session;
    /**
     * The chatbot client id at host
     * @var type 
     */
    public $client;
    /**
     * The chatbot provider app id - e.g. facebook page app id
     * @var type 
     */
    public $app;
    /**
     * The sender
     * @var type 
     */
    public $sender;
    /**
     * Constructor
     * @param array $data
     */
    public function __construct($client,$app,$sender) 
    {
        $this->client = $client;
        $this->app = $app;
        $this->sender = $sender;
    }
    /**
     * Get data in array form
     * @return array
     */
    public function toArray()
    {
        return [
            'client'=>$this->client,
            'app'=>$this->app,
            'sender'=>$this->sender,
        ];
    }
    /**
     * Get data in string
     * @return array
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }
    /**
     * Get the chatbot model
     * @see Chatbot::locateClient()
     */
    public function getChatbot()
    {
        if (!isset($this->_bot)){
            $chatbot = Chatbot::findClient($this->client);
            if ($chatbot!=null)
                $this->_bot = $chatbot;
            else
                throw new CException('Chatbot not found by client: '.$this->client);
        }
        return $this->_bot;
    }   
    /**
     * Get the chatbot session 
     */
    public function getSession()
    {
        if (!isset($this->_session)){
            $this->_session = md5($this->client.$this->app.$this->sender);
        }
        return $this->_session;
    }   
    /**
     * Return chatbot owner 
     * @return ChatbotModel 
     */
    public function getChatbotOwner($classPrefix='Chatbot') 
    {
        $modelClass = $classPrefix.ucfirst($this->chatbot->owner_type);
        return new $modelClass($this->chatbot->owner_id);
    }
    /**
     * Verify if chatbot user is a guest or login user
     * @return type
     */
    public function getIsGuest()
    {
        return !Yii::app()->user->hasSessionId($this);
    }
}
