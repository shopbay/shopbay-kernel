<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of AccountUnlinkView
 *
 * @author kwlok
 */
class AccountUnlinkView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        if ($this->context->isGuest){
            $model = $this->getMessengerModel();
            $callbackUrl = Yii::app()->getModule('chatbots')->getOAuthUrl([
                'client'=>$this->context->client,
            ]);
            $this->sendAccountLinkingTemplate($this->context->sender,Sii::t('sii','You are not logged in now or your session has expired. Please login again'),$callbackUrl);
        }
        else {
            $model = $this->getMessengerModel();
            $this->sendAccountUnlinkingTemplate($this->context->sender,Sii::t('sii','You can log out here.',['{app}'=>$model->name]));
        }
    }
}
