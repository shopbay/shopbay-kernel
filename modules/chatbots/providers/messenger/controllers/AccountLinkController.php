<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerBot');
/**
 * Account Link Event Processor
 * 
 * Account Linking allows you to invite users to log-in using your own authentication flow, and to receive a Messenger page-scoped ID (PSID) upon completion. 
 * We can then provide a more secure, personalized and relevant experience to users.
 * 
 * @see AccountLinkEvent
 * @see https://developers.facebook.com/docs/messenger-platform/account-linking
 * 
 * @author kwlok
 */
class AccountLinkController extends MessengerBot 
{
    CONST STATUS_LINKED   = 'linked';
    CONST STATUS_UNLINKED = 'unlinked';
    /**
     * Process an event 
     * @param AccountLinkEvent $event
     */
    public function process($event)
    {
        logInfo(__METHOD__." Received $event->className for user $event->sender with status $event->status and auth code $event->authCode at $event->timestampString");
        
        $user = $this->getChatbotUser($event->chatbot->client_id, $event->page, $event->sender);
        $context = new ChatbotContext($user->client_id, $user->app_id, $user->user_id);
        
        if ($event->status==self::STATUS_LINKED){
            $user->session_data = json_encode([
                'session_id'=>$context->session,
                'status'=>'linked',
                'authorization_code'=>$event->authCode,
                'timestamp'=>$event->timestamp,
            ]);
            if (Yii::app()->authManager->oauthLogin($context,$user)){
                //do additional after login stuff, if any
            }
        }
        elseif ($event->status==self::STATUS_UNLINKED){
            Yii::app()->authManager->oauthLogout($user);
        }
    }       
}