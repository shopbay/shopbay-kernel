<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerBot');
/**
 * Message Event Processor
 * @see MessageEvent
 * 
 * @author kwlok
 */
class MessageController extends MessengerBot 
{
    /**
     * Process an event 
     * @param MessageEvent $event
     */
    public function process($event)
    {
        $context = $this->createContext($event);
        if ($event->isEcho) 
            $this->processEcho($context,$event);
        elseif ($event->isQuickReply) 
            $this->processQuickReply($context,$event);
        elseif ($event->isAttachments) 
            $this->processAttachments($context,$event);
        elseif ($event->isText) 
            $this->processText($context,$event);
        else 
            logError(__METHOD__.' unknown event');
    }
    /**
     * Process a text message 
     * @param ChatbotContext $context
     * @param MessageEvent $event
     */
    protected function processText($context,$event)
    {
        logInfo(__METHOD__." Received $event->className for user $event->sender and page $event->recipient at $event->timestampString with message:",$event->data); 
        
        $receivedText = strtolower($event->text);//convert to lower case, make it case insensitive
        if (HelpPayload::hasCommand($receivedText)){
            //live chat start is being handled inside
            $this->processPayload($context,HelpPayload::generatePayload($receivedText));
        }
        else {
            switch ($receivedText) {
                 //todo should open to login user with admin rights
//                case '/thread setup':
//                    $this->processPayload($context,new MessengerPayload(MessengerPayload::THREAD_SETTINGS));
//                    break;        
                default://conversation starts
                    if (Yii::app()->user->onLiveChat($context)){
                        if (strtolower($event->text)=='bye')
                            $this->processLivechat($context, LiveChatPayload::END);
                        else //speak to human
                            $this->processLivechat($context, LiveChatPayload::RELAY, $event->text);
                    }
                    else {
                        //speak to bot
                        $this->sendTypingOn($event->sender);//as converse via api may not get immediate reply due to potential latency
                        if (param('WIT_AI_ON'))
                            $this->converse($context->client,$context->app,$context->sender,$event->text);
                        else {
                            $this->sendTextMessage($event->sender,Sii::t('sii','I probably could not understand you. Please type "help" if you need assistance.'));
                            logInfo(__METHOD__." Bot conversation not supported. Wit.ai is disabled");
                        }
                    }
                    break;
            }
        }
    }  
    /**
     * Process an echo message 
     * @param ChatbotContext $context
     * @param MessageEvent $event
     */
    protected function processEcho($context,$event)
    {
        logInfo(__METHOD__." Received echo of $event->className for message $event->messageId and app $event->appId with text '$event->text' and metadata '$event->metadata' at $event->timestampString");
        /**
         * @todo The first use case for Echo is live chat support
         * CAUTION: If to implement using echo message - take note of echoing and end up loopless messaging relaying
         * Need to put some flag to check and only show relay message once
         */
        /**
         * But it seems we have a easier solution not require 'echo' at all! Use BotUser session
         * @see MessengerBot::processLivechat()
         */
    }  
    /**
     * Process a quick reply message 
     * @param ChatbotContext $context
     * @param MessageEvent $event
     */
    protected function processQuickReply($context,$event)
    {
        logInfo(__METHOD__." Quick reply of $event->className for $event->messageId at $event->timestampString with payload", $event->quickReplyPayload);
        $this->processPayload($context,$event->quickReplyPayload);
    }  
    /**
     * Process a message with an attachment (image, video, audio)
     * @param ChatbotContext $context
     * @param MessageEvent $event
     */
    protected function processAttachments($context,$event)
    {
        logInfo(__METHOD__." Received $event->className for user $event->sender and page $event->recipient at $event->timestampString with message:",$event->data); 
        
        //start agent live chat session if not yet
        if (Yii::app()->user->onLiveChat($context)){
            $controller = new LiveChatController($this->token);
            $sessionMetadata = Yii::app()->user->getLiveChatMetadata($context);
            foreach ($event->attachments as $attachment) {
                $sessionMetadata->messageType = $attachment['type'];
                $sessionMetadata->url = $attachment['payload']['url'];
                $controller->processAttachment($context,$sessionMetadata);
            }
        }
        else {
            $this->sendTextMessage($event->sender, "Message with attachment received");
        }
    }  

}