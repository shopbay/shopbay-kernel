<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of VideoView
 *
 * @author kwlok
 */
class VideoView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param MessengerPayload $payload 
     */
    public function render($payload) 
    {
        $this->sendVideo($this->context->sender,$payload->params['url']);
    }
}
