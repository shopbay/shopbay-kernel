<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
Yii::import('common.modules.notifications.models.Notification');
/**
 * Description of ShopSubscriptionView
 *
 * @author kwlok
 */
class ShopSubscriptionView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $model->setSearchMethod(ChatbotModel::DB_SEARCH);//todo use elasticsearch only when Chinese chars search is working

        $notifications = Notification::subscriptionsConfig();
        
        $currentPage = $this->getCurrentPage($payload);
        
        $account = $this->context->isGuest?null:Yii::app()->user->getSessionAccount($this->context);
        
        $searchResult = $this->getSearchResult($model, 'searchNotificationSubscriptions', [$this->context,$account,$currentPage]);
        if ($searchResult->totalItemCount==0){
            $this->newSubscriptions($notifications);
        }
        else {
            $currentNotifications = [];
            $bubbles = [];//maximum 10 (but auto trimmed when limit exceeds)
            //for subscribed notifications
            foreach ($searchResult->data as $notification) {
                $currentNotifications[] = $notification->name;
                $bubbles[] = $this->renderBubble($payload, $notification->name, false);
                
            }
            //for unsubscribed notifications
            foreach ($notifications as $notification => $config) {
                if (!in_array($notification, $currentNotifications)){
                    if (!isset($config['accountRequired']) || 
                        (isset($config['accountRequired']) && $config['accountRequired'] && !$this->context->isGuest))
                        $bubbles[] = $this->renderBubble($payload, $notification, true);
                }
            }
            
            if (!empty($bubbles)){
                $this->sendTextMessage($this->context->sender, Sii::t('sii','You can subscribe or unsubscribe following notifications'));
                $this->sendGenericTemplate($this->context->sender,$bubbles);
            }
        }
    }
    /**
     * Get bubble
     * @param type $payload
     * @param type $notification
     * @return \PostbackButton
     */
    protected function renderBubble($payload,$notification,$subscribe=true)
    {
        $buttons = [];//maximum 3 (but auto trimmed when limit exceeds)
        if ($subscribe){
            $subscribePayload = new ShopPayload(ShopPayload::SUBSCRIBE,['notification'=>$notification]);
            $buttons[] = new PostbackButton(Sii::t('sii','Subscribe'), $subscribePayload->toString());
        }
        else {
            $unsubscribePayload = new ShopPayload(ShopPayload::UNSUBSCRIBE,['notification'=>$notification]);
            $buttons[] = new PostbackButton(Sii::t('sii','Unsubscribe'), $unsubscribePayload->toString());
        }
        return new Bubble(Notification::siiName()[$notification], Notification::siiDescription()[$notification], null, null, $buttons);
    }    
    /**
     * Show notifications that is available for subscribe
     */
    protected function newSubscriptions($notifications)
    {
        $message = Sii::t('sii','Would you like to receive following notifications? You can change these settings any time.');
        $this->sendTextMessage($this->context->sender, $message);
        $bubbles = [];//maximum 10 (but auto trimmed when limit exceeds)
        foreach ($notifications as $notification => $config) {
            $buttons = [];
            if (!isset($config['accountRequired']) || 
                (isset($config['accountRequired']) && $config['accountRequired'] && !$this->context->isGuest)){
                $subscribePayload = new ShopPayload(ShopPayload::SUBSCRIBE,['notification'=>$notification]);
                $buttons[] = new PostbackButton(Sii::t('sii','Subscribe'), $subscribePayload->toString());
                $bubbles[] = new Bubble(Notification::siiName()[$notification], Notification::siiDescription()[$notification], null, null, $buttons);
            }
        }
        $this->sendGenericTemplate($this->context->sender,$bubbles);
    }   
}
