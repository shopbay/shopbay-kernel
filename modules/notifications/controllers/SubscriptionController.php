<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagelayout.SPageLayout');
/**
 * Description of SubscriptionController
 *
 * @author kwlok
 */
class SubscriptionController extends AuthenticatedController
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        //load layout and common css/js files
        $this->module->registerScripts();
    }
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return [
            'index'=>[
                'class'=>'common.modules.notifications.actions.SubscribeNotificationAction',
            ],               
        ];
    }       
    /**
     * Get notifications data
     * @return type
     */
    public function prepareNotifications()
    {
        $notifs = NotificationRegister::{'get'.user()->currentRole.'Notifications'}();
        foreach ($notifs as $name => $config) {
            foreach ($config['channels'] as $index => $channel) {
                $sub = Yii::app()->serviceManager->notificationManager->findSubscription(user()->getId(),$name,$channel,new NotificationScope(user()->getId()));
                if ($sub==null || $sub->online())//default is on subscription
                    $notifs[$name]['subscription'][Notification::encodeKey($name, $channel)] = true;
                elseif ($sub->offline())
                    $notifs[$name]['subscription'][Notification::encodeKey($name, $channel)] = false;
            }
        }
        //logTrace(__METHOD__,$notifs);
        return $notifs;
    }
}
