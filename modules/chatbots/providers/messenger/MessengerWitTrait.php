<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * MessengerWitTrait is to provide the method implementation for {@link WitBot} and {@link WitActionInterface}
 * 
 * @author kwlok
 */
trait MessengerWitTrait 
{
    /**
     * Get session object; 
     * By default all sensitive user information are digested befor sending to Wit 
     * @param type $sessionId
     * @param $digest If to restore values
     * @return WitSession
     */
    protected function getWitSession($sessionId,$digest=true)
    {
        return $this->parseWitSession($sessionId,$digest);
    }
    /**
     * Say action 
     * @see WitBot
     */
    public function say($sessionId,$message,$context,$entities)
    {
        $session = $this->getWitSession($sessionId,false);
        $this->sendTextMessage($session->user, $message);
        if ($context->has(WitContext::PAYLOAD)){
            $payload = ChatbotPayload::decode($context->get(WitContext::PAYLOAD));
            $chatbotContext = new ChatbotContext($session->client, $session->app, $session->user);
            $this->processPayload($chatbotContext,$payload);
        }
    }
    /**
     * @inheritdoc
     */
    public function findProduct($sessionId, $context, $entities = [])
    {
        $query = WitContext::getFirstEntityValue(WitEntity::SEARCH_QUERY, $entities);

        if (!$query) {
            return new WitContext([WitContext::PRODUCT_NOT_FOUND => true]);
        }
        
        $session = $this->getWitSession($sessionId);
        $payload = new ShopPayload(ShopPayload::PRODUCTS,['query'=>$query]);

        return new WitContext([
            WitContext::PRODUCT_MESSAGE => 'Here are what we have found for "'.$query.'".',
            WitContext::PAYLOAD =>$payload->toString(),
        ]);
    }
    /**
     *  @inheritdoc
     */
    public function findGreetingPerson($sessionId, $context, $entities = [])
    {
        $session = $this->getWitSession($sessionId,false);
        $greetingPerson = $this->getUserProfile($session->user);
        return new WitContext([
            WitContext::GREETING_PERSON => $greetingPerson->firstName,
        ]);
    }
}
