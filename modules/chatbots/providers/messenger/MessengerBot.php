<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.components.ChatbotContext');
Yii::import('common.modules.chatbots.models.ChatbotUser');
Yii::import('common.modules.chatbots.payloads.*');
Yii::import('common.modules.chatbots.providers.messenger.MessengerTrait');
Yii::import('common.modules.chatbots.providers.messenger.MessengerWitTrait');
Yii::import('common.modules.chatbots.ai.wit.WitBot');
/**
 * Description of MessengerBot
 * 
 * Note: One can trigger payload to sending messages.
 * <pre>
 * //Example:
 * $payloadType = MessengerPayload::IMAGE;//payload type can be of any supported types
 * $processor = new MessageController();
 * $processor->processPayload(new MessengerPayload($payloadType, $event->page, $event->sender,['url'=>$this->getAssetURL('sample.png')]));
 * </pre>
 * where <code>$event</code> is instance of {@link MessengerEvent} or its child class.
 * 
 * @see PayloadController::process() for various payload handling
 * 
 * @author kwlok
 */
class MessengerBot extends WitBot 
{
    use MessengerTrait, MessengerWitTrait;
    /**
     * Process an event 
     * @param MessengerEvent $event
     */
    public function process($event)
    {
        throw new CException('Please implement logic at child class');
    }
    /**
     * Create a new chatbot context
     * @param type $event
     * @return \ChatbotContext
     */
    public function createContext($event)
    {
        return new ChatbotContext($event->chatbot->client_id, $event->page, $event->sender);
    }
    /**
     * Process a payload
     * @param ChatbotContext $context 
     * @param MessegnerPayload|string $payload Either object or encoded string
     */
    public function processPayload($context,$payload)
    {
        $controller = new PayloadController($this->token);
        if ($payload instanceof LiveChatPayload)//for livechat
            $controller->processData($context,$payload->toString());
        elseif ($payload instanceof ChatbotPayload)
            $controller->processData($context,$payload->toString());
        else
            $controller->processData($context,$payload);//already encoded
    }
    /**
     * Process live chat payload
     * @param type $context
     * @param type $status
     * @param type $text
     */
    public function processLivechat($context, $status, $text=null) 
    {
        $metadata = new LiveChatMetadata($status, $context->sender);
        if (isset($text))//for message relay
            $metadata->text = $text;
        (new LiveChatController($this->token))->processMetaData($context,$metadata);
    }    
    /**
     * Return asset url
     */
    protected function getAssetURL($filename)
    {
        $assetsURL = Yii::app()->controller->getAssetsURL('common.modules.chatbots.providers.messenger.assets');
        return $assetsURL.'/'.$filename;
    }      
    /**
     * Format time stamp (epoch time in milliseconds) => 13 digits
     * @param int $timestamp in milliseconds
     * 
     */
    public function formatTimestamp($timestamp)
    {
        return date('Y/m/d H:i:s',$timestamp/1000);
    }   
    /**
     * Get chatbot user
     * @return type
     * @throws CException
     */
    protected function getChatbotUser($client_id, $app_id, $user_id)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'client_id'=>$client_id,
            'app_id'=>$app_id,
            'user_id'=>$user_id,
        ]);
        $user = ChatbotUser::model()->find($criteria);
        if ($user===null){
            $user = new ChatbotUser();
            $user->client_id = $client_id;
            $user->app_id = $app_id;
            $user->user_id = $user_id;
            $user->save();//save record
            logTrace(__METHOD__.' chatbot user created',$user->attributes);
        }
        return $user;
    }    
}