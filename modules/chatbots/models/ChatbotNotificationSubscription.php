<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
        Yii::import('common.modules.notifications.models.NotificationSubscription');
/**
 * Description of ChatbotNotificationSubscription
 *
 * @author kwlok
 */
class ChatbotNotificationSubscription extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'NotificationSubscription';
    }
    /**
     * Get model name
     * @return type
     */
    public function getName($locale=null)
    {
        return $this->model->notification;
    }    
}
