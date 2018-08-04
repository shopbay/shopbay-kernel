<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of LiveChatRelayView
 *
 * @author kwlok
 */
class LiveChatRelayView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param LiveChatPayload $payload 
     */
    public function render($payload) 
    {
        $metadata = LiveChatMetadata::decode($payload->params['metadata']);
        if ($metadata->fromAgent){//sender is agent
            //Relay message to Customer
            $message = Sii::t('sii','{sender}: {message}',[
                '{sender}'=>$metadata->agentName,
                '{message}'=>$metadata->text,
            ]);
            $this->sendTextMessage($metadata->customer,$message);
        }
        else {
            //Relay message to Agent
            $message = Sii::t('sii','{sender}: {message}',[
                '{sender}'=>$metadata->customerName,
                '{message}'=>$metadata->text,
            ]);
            $this->sendTextMessage($metadata->agent,$message);
        }
    }    
}
