<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of LiveChatEndView
 *
 * @author kwlok
 */
class LiveChatEndView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param LiveChatPayload $payload 
     */
    public function render($payload) 
    {
        $metadata = LiveChatMetadata::decode($payload->params['metadata']);
        
        $this->sendTextMessage($metadata->customer, Sii::t('sii','Live chat exited.'),$metadata->toString());

        $this->sendTextMessage($metadata->agent, Sii::t('sii','{customer} has left live chat session.',['{customer}'=>$metadata->customerName]),$metadata->toString());
    }    
}
