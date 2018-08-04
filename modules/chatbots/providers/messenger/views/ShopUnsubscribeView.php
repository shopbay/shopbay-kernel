<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
Yii::import('common.modules.notifications.models.Notification');
/**
 * Description of ShopUnsubscribeView
 *
 * @author kwlok
 */
class ShopUnsubscribeView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        //handles single un-subscription
        if (isset($payload->params['notification'])){
            $notification = $payload->params['notification'];
            $model = $this->getMessengerModel();
            $model->unsubscribeNotification($notification,$this->context);
            $text = Sii::t('sii','You have unsubscribed {notification}.',['{notification}'=>Notification::siiName()[$notification]]);
            $text .= ' '.Sii::t('sii','To manage your subscriptions, type "subscription".');
            $this->sendTextMessage($this->context->sender,$text);
        }
    }
}
