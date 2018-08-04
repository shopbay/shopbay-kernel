<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.ai.wit.*');
/**
 * Description of WitBot
 *
 * @author kwlok
 */
abstract class WitBot extends CComponent implements WitActionInterface
{
    /**
     * The wit application 
     * @var type 
     */
    protected $witApp;
    /**
     * Create wit session
     * @param $client
     * @param $app
     * @param $user
     * @return WitSession
     */
    protected function createWitSession($client,$app,$user)
    {
        return new WitSession([
            'client' => $client,
            'app' => $app,
            'user' => $user,
        ]);
    } 
    /**
     * Parse wit session
     * @param $sessionId
     * @param $digest If to digest values
     * @return WitSession
     */
    protected function parseWitSession($sessionId,$digest=true)
    {
        return WitSession::decode($sessionId,$digest);
    } 
    /**
     * Converse with Wit
     * @param string $client the chatbot client id
     * @param string $app the chatbot provider app id
     * @param type $user
     * @param type $message
     */
    protected function converse($client,$app,$user,$message)
    {
        $session = $this->createWitSession($client,$app,$user);
        $this->wit->converse(new WitActionMapping($this),$session->toString(),$message);
    } 
    /**
     * Get Wit app
     * @see Wit
     * @param string $token
     */
    protected function getWit($token = null)
    {
        if (!isset($this->witApp)){
            require_once(__DIR__.'/WitAutoloader.php');
            WitAutoloader::register();
            $this->witApp = new WitApp($token);
            logInfo(__METHOD__.' ok');
        }
        return $this->witApp;
    }   
}
