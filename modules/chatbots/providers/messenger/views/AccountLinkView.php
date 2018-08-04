<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of AccountLinkView
 *
 * @author kwlok
 */
class AccountLinkView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        if ($this->context->isGuest){
            $this->sendAccountLinkingTemplate($this->context->sender,$this->loginData['message'],$this->loginData['callbackUrl']);
        }
        else {
            $this->sendTextMessage($this->context->sender,Sii::t('sii','You have already logged in.'));
        }
    }
    
    public function getLoginData()
    {
        return [
            'message'=>Sii::t('sii','If you have account with us, login to get a more secure, personalized and better experience.'),
            'callbackUrl'=>Yii::app()->getModule('chatbots')->getOAuthUrl([
                'client'=>$this->context->client,
            ]),
        ];
    }
            
}
