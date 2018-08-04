<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
/**
 * Description of LiveChatPayload
 *
 * @author kwlok
 */
class LiveChatPayload extends ChatbotPayload 
{
    CONST START  = 'start';//start live chat session
    CONST END    = 'end';//end live chat session
    CONST RELAY  = 'relay';//relay message between "from" and "to"    
    CONST CLOSE  = 'close';//live chat support is closed    
    /**
     * Constructor.
     * @param string $type The payload type
     * @param array $params Additional parameters
     */
    public function __construct($type,$params=[],$typePrefix=true)
    {
        switch ($type) {
            case self::START:
                $params = array_merge($params,['status'=>self::START]);
                break;
            case self::END:
                $params = array_merge($params,['status'=>self::END]);
                break;
            case self::RELAY:
                $params = array_merge($params,['status'=>self::RELAY]);
                break;
            case self::CLOSE:
                $params = array_merge($params,['status'=>self::CLOSE]);
                break;
            default:
                break;
        }
        parent::__construct($type, $params,$typePrefix);
    }        
    /**
     * Type prefix
     * @return string
     */
    public function getTypePrefix()
    {
        return ChatbotPayload::LIVE_CHAT;
    }        
}