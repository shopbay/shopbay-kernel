<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ShopCustomPageView
 *
 * @author kwlok
 */
class ShopCustomPageView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $locale = null;//todo
        $buttons = [];
        
        if (isset($payload->params['page_name']) && isset($payload->params['page_url']))
            $buttons[] = new WebUrlButton($payload->params['page_name'],$payload->params['page_url']);
        elseif (isset($payload->params['page'])) {
            $page = Page::model()->locateOwner($model)->active()->locatePage($payload->params['page'])->find();
            if ($page!=null)
                $buttons[] = new WebUrlButton($page->displayLanguageValue('title',$locale),$page->getUrl(true));
        }
           
        $message = Sii::t('sii','You can click link below to view page.');
        //todo Ecan try to extract  summary content from $page instead of showing this generic message
        $this->sendButtonTemplate($this->context->sender,$message,$buttons);
    }
}
