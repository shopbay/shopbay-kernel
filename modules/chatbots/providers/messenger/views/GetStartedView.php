<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of GetStartedView
 *
 * @author kwlok
 */
class GetStartedView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param MessengerPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $profile = $this->getUserProfile($this->context->sender);
        $userFirstName = ' ';
        if ($profile!=null)
            $userFirstName .= $profile->firstName;

        $buttons = [];
        //1
        $categoriesPayload = new ShopPayload(ShopPayload::CATEGORIES);
        $buttons[] = new PostbackButton('Browse by category', $categoriesPayload->toString());
        //2
        $brandsPayload = new ShopPayload(ShopPayload::BRANDS);
        $buttons[] = new PostbackButton('Browse by brand', $brandsPayload->toString());
        //3
        $livechatPayload = new LiveChatPayload(LiveChatPayload::START);
        $buttons[] = new PostbackButton('Talk to support', $livechatPayload->toString());
        //$buttons[] = new WebUrlButton('Visit website',$model->url);
        
        $message = Sii::t('sii','Hi{user}, welcome to {shop}! What are you looking for today?',['{user}'=>$userFirstName,'{shop}'=>$model->name]);
        $message .= ' '.Sii::t('sii','You can use a few words or phrases, for example, “search _keywords_”, “show me _keywords_” or “best sellers” etc to find you the stuff you are interested.');
        $message .= ' '.Sii::t('sii','Or, just type “help” anytime if you need more assistance.');
        $this->sendButtonTemplate($this->context->sender,$message,$buttons);

//        if ($this->context->isGuest){
//            $accountLinkView = new AccountLinkView($this->token);
//            $this->sendAccountLinkingTemplate($this->context->sender,$accountLinkView->loginData['message'],$accountLinkView->loginData['callbackUrl']);
//        }        
        
//        if ($model->hasCampaignSale){
//            $this->sendTextMessage($this->context->sender, $model->campaignSale->text);
//        }
        
    }

}
