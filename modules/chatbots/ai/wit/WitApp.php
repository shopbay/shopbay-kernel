<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
//use Tgallice\Wit\Client;
use Tgallice\Wit\Conversation;
use Tgallice\Wit\Model\Context;
/**
 * The wit application
 *
 * @author kwlok
 */
class WitApp extends CComponent
{
    /**
     * Wit application access token
     * @var 
     */
    protected $token;
    /**
     * Constructor
     * @param string $token The Wit application access token
     */
    public function __construct($token = null)
    {
        if ($token==null)
            $token = readConfig('wit', 'accessToken');
        
        $this->token = $token; 
    }    
    /**
     * Converse with Wit application
     * @param type $sessionId
     * @param type $message
     */
    public function converse($actionMapping,$sessionId,$message)
    {
        $client = new WitClient($this->token);
        $api =  new WitConverseApi($client);
        $conversation = new Conversation($api, $actionMapping);
        $context  = new Context();
        $context = $conversation->converse($sessionId, $message, $context);
    }
}
