<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerBot');
Yii::import('common.modules.notifications.models.Notification');
/**
 * Opt In Event Processor
 * @see OptInEvent
 * 
 * @author kwlok
 */
class OptInController extends MessengerBot 
{
    /**
     * Process an event 
     * @param OptInEvent $event
     */
    public function process($event)
    {
        logInfo(__METHOD__." Received $event->className for user $event->sender and page $event->recipient at $event->timestampString with pass through param", $event->passThroughParam);

        $context = $this->createContext($event);

        $model = $context->getChatbotOwner();

        //[1]opt in to subscribe notification
        $payload1 = $this->getOptInPayload($event);
        if ($payload1 instanceof OptInPayload){
            $this->runSubscriptions ($context, $model, $payload1);
            return;
        }
        
        //[2]opt in to register as live chat agent
        $payload2 = $this->getLiveChatAgentPayload($event);
        if ($payload2 instanceof LiveChatAgentPayload){
            $this->registerAgent($context, $model, $payload2);
            return;
        }
    }
    /**
     * Get the live chat agent payload 
     */
    protected function getLiveChatAgentPayload($event)
    {   
        return LiveChatAgentPayload::decode($event->passThroughParam);   
    } 
    /**
     * Register agent
     * 
     * @see ChatbotSupportForm attributes $agentId
     * @param type $context
     * @param type $payload
     */
    protected function registerAgent($context,$model,$payload)
    {
        $settings = ChatbotSupport::prepareAgentData($context->sender, $payload->account);
        $context->chatbot->saveSettings($settings);
        $message = ' '.Sii::t('sii','Done! You are now a live chat agent for {shop}.',['{shop}'=>$model->name]);
        $this->sendTextMessage($context->sender,$message);
    }
    /**
     * Get the opt-in payload 
     */
    protected function getOptInPayload($event)
    {   
        return OptInPayload::decode($event->passThroughParam);   
    } 
    
    protected function runSubscriptions($context,$model,$payload)
    {
        foreach ($payload->notifications as $notification) {
            $model->subscribeNotification($notification,$context,$payload->account);
        }

        $message1 = ' '.Sii::t('sii','Welcome to {shop}! Enjoy a new convesational shopping experience with our personal shopping assistant. Type “help” anytime if you need more assistance.',['{shop}'=>$model->name]);
        $this->sendTextMessage($context->sender,$message1);

        $message2 = Sii::t('sii','Thanks for your subscription to {updates}.',['{updates}'=>$payload->notificationDisplayNames]);
        $message2 .= ' '.Sii::t('sii','To manage your subscriptions, type "subscription".');
        $this->sendTextMessage($context->sender,$message2);

        if ($payload->hasAccount){
            //set session account when user account id is available 
            Yii::app()->user->setSessionId($context);
            Yii::app()->user->setSessionAccount($context,$payload->account);
            $this->showAccountRequiredSubscriptions($context,$model,$payload->account);
        }
    }
    /**
     * Show notifications that is is account required - since now user is linked
     */
    protected function showAccountRequiredSubscriptions($context,$model,$account)
    {
        $subscribedNotifications = $this->getAccountSubscriptions($context, $model, $account);
        $bubbles = [];//maximum 10 (but auto trimmed when limit exceeds)
        foreach (OptInPayload::getAccountRequiredNotifications() as $notification => $alias) {
            
            if (!in_array($notification, $subscribedNotifications)){
                //only shows unsubscribed notifications
                $type = ShopPayload::SUBSCRIBE;
                $title = Sii::t('sii','Subscribe');
                $buttons = [];
                $payload = new ShopPayload($type,['notification'=>$notification]);
                $buttons[] = new PostbackButton($title, $payload->toString());
                $bubbles[] = new Bubble(Notification::siiName()[$notification], Notification::siiDescription()[$notification], null, null, $buttons);
            }
        }
        if (!empty($bubbles)){
            $message = Sii::t('sii','Would you also like to receive following notifications? You can change these settings any time.');
            $this->sendTextMessage($context->sender, $message);
            $this->sendGenericTemplate($context->sender,$bubbles);
        }
    }       
    
    protected function getAccountSubscriptions($context,$model,$account)
    {
        $searchResult = $model->searchNotificationSubscriptions($context,$account,0,null);//current page = 0 (1 page display only)
        $subscribedNotifications = [];
        //for subscribed notifications
        foreach ($searchResult->data as $notification) {
            $subscribedNotifications[] = $notification->name;
        }
        return $subscribedNotifications;
    }
}