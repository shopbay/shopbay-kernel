<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerPayload');
/**
 * Description of LiveChatMetadata
 *
 * @author kwlok
 */
class LiveChatMetadata extends CComponent
{
    public $status;//according to payload type
    public $appName;//app name, either a shop name or object name owning the chatbot
    public $sender;//sender messenger id 
    public $messageType = MessengerPayload::TEXT;//default is text message, can be attachment payload as well
    public $customer;//customer messenger id
    public $customerName;//customer name
    public $agent;//agent messenger id  
    public $agentName;//agent name
    public $text;//relay text
    public $url;//relay attachment url
    
    public function __construct($status,$sender,$text=null) 
    {
        $this->status = $status;
        $this->sender = $sender;
        $this->text = $text;
    }
    
    public function toArray() 
    {
        $metadata = new LiveChatMetadata('status','sender');//dummy values
        $reflect = new ReflectionClass($metadata);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        $array = [];
        foreach ($props as $prop) {
            $field = $prop->getName();
            $array[$field] = $this->$field;
        }
        return $array;
    }
    
    public function toString()
    {
        return json_encode($this->toArray());
    }
    
    public function getFromAgent()
    {
        return $this->agent == $this->sender;
    }
    
    public function getIsClosed()
    {
        return $this->status == LiveChatPayload::CLOSE;
    }
    
    public function getIsAttachment()
    {
        return in_array($this->messageType, [
            MessengerPayload::IMAGE,
            MessengerPayload::AUDIO,
            MessengerPayload::VIDEO,
            MessengerPayload::FILE,
        ]);
    }
    
    public static function decode($encodedString)
    {
        $metadata = json_decode($encodedString,true);
        if (is_array($metadata) && 
            isset($metadata['status']) && 
            isset($metadata['sender'])){
            $obj = new LiveChatMetadata($metadata['status'], $metadata['sender']);
            unset($metadata['status']);
            unset($metadata['sender']);
            foreach ($metadata as $key => $value) {
                //load other attributes
                $obj->$key = $value;
            }
            return $obj;
        }
        else
            return null;
    }
    
}