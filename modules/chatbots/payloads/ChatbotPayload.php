<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ChatbotPayload
 *
 * @author kwlok
 */
class ChatbotPayload extends CComponent 
{
    CONST SEPARATOR = '%%';
    /*
     * Payload type
     */
    CONST HELP              = 'help';
    CONST OPT_IN            = 'optIn';
    /*
     * Payload type prefix
     */
    CONST SHOP              = 'shop_';
    CONST PRODUCT           = 'product_';
    CONST TREND             = 'trend_';
    CONST SHIPPING          = 'shipping_';
    CONST LIVE_CHAT         = 'live_chat_';
    /**
     * Payload type
     * @var string 
     */
    public $type;
    /**
     * Additional Payload params
     * @var array 
     */
    public $params = [];
    /**
     * Constructor.
     * @param string $type The payload type
     * @param array $params Additional parameters
     */
    public function __construct($type,$params=[],$typePrefix=true)
    {
        $this->type = $typePrefix?$this->typePrefix.$type:$type;
        $this->params = $params;
    }    
    /**
     * Type prefix
     * @return string
     */
    public function getTypePrefix()
    {
        return null;//default to empty
    }    
    /**
     * Check if payload contains params 
     * @return boolean
     */
    public function getHasParams()
    {
        return !empty($this->params);
    }        
    /**
     * Serialize payload
     * @return type
     */
    public function toString()
    {
        return self::encode(get_class($this),$this->type, $this->params);
    }
    /**
     * Encoding payload with paramaters
     * @param string $class The payload class
     * @param string $type The payload type
     * @param array $params The additional payload params
     * @return string
     */
    public static function encode($class,$type,$params=[])
    {
        $encodingParams = ['class'=>$class];
        if (!empty($params))
            $encodingParams = array_merge($encodingParams,['params'=>$params]);
        return $type.self::SEPARATOR.json_encode($encodingParams);
    }
    /**
     * Decoding payload and resume the payload instance
     * @param string $encodedPayload
     * @return mixed
     */
    public static function decode($encodedPayload)
    {
        $payload = explode(self::SEPARATOR, $encodedPayload);
        $type = $payload[0];
        $data = json_decode($payload[1],true);
        if (isset($data['class'])){
            $class = $data['class'];
            if (isset($data['params']))
                return new $class($type,$data['params'],false);//decoding no need type prefix
            else
                return new $class($type,[],false);//decoding no need type prefix
        }
        else
            throw new CException('Payload class not found');
    }
}
