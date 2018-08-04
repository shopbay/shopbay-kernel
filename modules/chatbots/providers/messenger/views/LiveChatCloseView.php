<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of LiveChatCloseView
 *
 * @author kwlok
 */
class LiveChatCloseView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param LiveChatPayload $payload 
     */
    public function render($payload) 
    {
        $message = Sii::t('sii','Our Live Chat support is currently closed.');

        if (count($payload->params)>1){//excluding in-built 'status' params
            $openTime = $payload->params['open_time'];
            $closeTime = $payload->params['close_time'];
            $offDays = $payload->params['off_days'];
            $message .= "\n\n".Sii::t('sii','Our live chat service is available from {open_time} to {close_time}, excluding {off_days}.',['{open_time}'=>$openTime,'{close_time}'=>$closeTime,'{off_days}'=>$offDays]);
        }
        
        $message .= "\n\n".Sii::t('sii','If you have any enquiries, please send us email at {email}',['{email}'=>$this->context->chatbotOwner->model->email]);
        $this->sendTextMessage($this->context->sender,$message);
    }    
}
