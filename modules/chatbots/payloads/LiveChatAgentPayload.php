<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
/**
 * Description of LiveChatAgentPayload
 * This is for merchant account to register as live chat support agent
 * 
 * @author kwlok
 */
class LiveChatAgentPayload extends ChatbotPayload 
{
    CONST AGENT  = 'agent';//live chat support agent payload type    
    /**
     * Params key to be kept as short as possible to keep its to string length the shortest
     */
    public static $accountParamKey  = 'a';
    /**
     * Constructor.
     * @param integer $host The host id
     * @param string $account_id If the user account id to register as agent
     */
    public function __construct($account_id)
    {
        $params = [
            static::$accountParamKey => $account_id,
        ];
        parent::__construct(self::AGENT, $params, false);//typePrefix false
    }    
    /**
     * Get the account user
     */
    public function getAccount()
    {
        return $this->params[static::$accountParamKey];
    }   
    /**
     * Serialize as string
     * @return string
     */
    public function toString()
    {
        return base64_encode(ChatbotPayload::encode(get_class($this), $this->type, $this->params));
    }    
    /**
     * Decode base64 first 
     * @param string $encodedPayload
     * @return mixed
     */
    public static function decode($encodedPayload)
    {
        $payloadString = base64_decode($encodedPayload);
        
        $payload = explode(self::SEPARATOR, $payloadString);
        $type = $payload[0];
        
        if (isset($payload[1])){
            $data = json_decode($payload[1],true);
            logInfo(__METHOD__.' payload data', $data);
            if (isset($data['class'])){
                $class = $data['class'];
                if (isset($data['params']) && 
                    isset($data['params'][static::$accountParamKey]))
                    return new $class($data['params'][static::$accountParamKey]);//decoding no need type prefix
            }
        }
        
        return null;
    }    
}
