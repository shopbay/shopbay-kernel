<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of GreetingView
 *
 * @author kwlok
 */
class GreetingView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $text = $this->context->chatbot->getSetting('greetingText');
        if ($this->sendGreetingText($text))
            $this->sendTextMessage($this->context->sender, "Greeting text '$text' reset successfully.");
        else
            $this->sendTextMessage($this->context->sender, Sii::t('sii','Greeting text failed to reset.'));
    }
}
