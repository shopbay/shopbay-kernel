<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of PersistentMenuView
 *
 * @author kwlok
 */
class PersistentMenuView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param MessengerPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        
        $menus = $model->getPesistentMenuItems();
        
        if ($this->sendPersistenMenu($menus))
            $this->sendTextMessage($this->context->sender, Sii::t('sii','Persistent Menu reset successfully.'));
        else
            $this->sendTextMessage($this->context->sender, Sii::t('sii','Persistent Menu failed to reset.'));
    }

}
