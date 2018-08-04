<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.notifications.models.Notification');
Yii::import('common.modules.notifications.models.NotificationScope');
/**
 * Description of SubscribeNotificationAction
 *
 * @author kwlok
 */
class SubscribeNotificationAction extends CAction 
{
    public $viewFile = 'index';
    
    public function run()
    {
        $this->controller->setPageTitle(Sii::t('sii','Notifications'));
        
        if (!empty($_POST)){
            logTrace(__METHOD__.' $_POST',$_POST);
            unset($_POST[param('CSRF_TOKEN_NAME')]);
            foreach ($_POST as $key => $subscribe) {
                $notification = Notification::decodeKey($key);
                $service = $subscribe?'subscribe':'unsubscribe';
                Yii::app()->serviceManager->notificationManager->{$service}(
                        user()->getId(),
                        $notification->name,
                        new NotificationScope(user()->getId()),//default to use global scope
                        $notification->type);
            }            
            user()->setFlash($this->controller->id,array(
                    'message'=>Sii::t('sii','Notifications are saved successfully.'),
                    'type'=>'success',
                    'title'=>Sii::t('sii','Notification Subscriptions')));
            unset($_POST);
        }
        
        $this->controller->render($this->viewFile);
    }
    
}
