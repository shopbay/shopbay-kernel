<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ChatbotEvent
 *
 * @author kwlok
 */
class ChatbotEvent extends CEvent
{
    /**
     * @var string The chatbot
     */
    public $chatbot;
    /**
     * Constructor.
     * @param string $chatbot The chatbot
     * @param string $sender 
     * @param array $data the event data
     */
    public function __construct($chatbot,$sender,$data)
    {
        $this->chatbot = $chatbot;
        parent::__construct($sender,$data);
    }    
}
