<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of GetStartedButtonView
 *
 * @author kwlok
 */
class GetStartedButtonView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param MessengerPayload $payload 
     */
    public function render($payload) 
    {
        $getStartedPayload = new MessengerPayload(MessengerPayload::GET_STARTED);
        $newPayload = new PayloadMenuItem($getStartedPayload->toString());
        if ($this->sendGetStartedButton($newPayload))
            $this->sendTextMessage($this->context->sender, Sii::t('sii','Get Started Button reset successfully.'));
        else
            $this->sendTextMessage($this->context->sender, Sii::t('sii','Get Started Button failed to reset.'));
    }

}
