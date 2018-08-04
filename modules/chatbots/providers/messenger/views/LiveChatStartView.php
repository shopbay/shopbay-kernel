<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of LiveChatStartView
 *
 * @author kwlok
 */
class LiveChatStartView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param LiveChatPayload $payload 
     */
    public function render($payload) 
    {
        $metadata = LiveChatMetadata::decode($payload->params['metadata']);
        if ($metadata->fromAgent){//sender is agent
            //Relay message to Agent
            $message3 = Sii::t('sii','Customer {customer} has initiated a new live chat session',['{customer}'=>$metadata->customerName]);
            $this->sendTextMessage($metadata->agent,$message3,$metadata->toString());
        }
        else {
            $message1 = Sii::t('sii','Welcome to {app} Live Chat support',['{app}'=>$metadata->appName]);
            
            //todo if outside business hours, show message like: Our live chat service is only available from "xx" to "yy", which day to which day.
            //if you have urgent contact us at: xxxx
            $message1 .= "\n\n".Sii::t('sii','To exit live chat, type "bye"');
            $this->sendTextMessage($metadata->customer,$message1,$metadata->toString());

            $message2 = Sii::t('sii','You are speaking with a live agent. How may I assist you? {agent}',['{agent}'=>$metadata->agentName]);
            $this->sendTextMessage($metadata->customer,$message2,$metadata->toString());
        }
    }    
}
