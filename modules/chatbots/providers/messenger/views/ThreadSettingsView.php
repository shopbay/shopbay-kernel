<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ThreadSettingsView
 *
 * @author kwlok
 */
class ThreadSettingsView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $options = [];
        //1
        $persistentMenuPayload = new MessengerPayload(MessengerPayload::PERSISTENT_MENU);
        $options[] = new QuickReply('Persistent Menu', $persistentMenuPayload->toString());
        //2
        $getStartedButtonPayload = new MessengerPayload(MessengerPayload::GET_STARTED_BUTTON);
        $options[] = new QuickReply('Get Started Button', $getStartedButtonPayload->toString());
        //3
        $greetingPayload = new MessengerPayload(MessengerPayload::GREETING);
        $options[] = new QuickReply('Greeting Text', $greetingPayload->toString());
        
        $this->sendQuickReplies($this->context->sender,'Select menu to reset',$options);
    }
}
