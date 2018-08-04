<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.ShopProductsPageView');
/**
 * Description of ShopProductUpdatesView
 *
 * @author kwlok
 */
class ShopProductUpdatesView extends ShopProductsPageView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $model->setSearchMethod(ChatbotModel::DB_SEARCH);//todo use elasticsearch only when Chinese chars search is working

        $hours = $payload->params['hours'];//last n hours
        
        $currentPage = $this->getCurrentPage($payload);
        $searchResult = $this->getSearchResult($model, 'searchLastestProducts', [$hours,$currentPage]);
        $bubbles = $this->renderBubbles($searchResult, $payload);
        
        if (!empty($bubbles)){
            $this->sendTextMessage($this->context->sender,Sii::t('sii','New arrivals! Check out our latest products added in the last {n} hours.',['{n}'=>$hours]));
            $this->sendGenericTemplate($this->context->sender,$bubbles);
            $this->renderSummaryText(new ShopPayload(ShopPayload::PRODUCT_UPDATES,['hours'=>$hours]), $searchResult, $currentPage);
        }
        /**
         * For subscription based notification, always return true sicne there is no error
         * @see Messenger::send()
         * @see SubscriberCommand::actionIndex()
         */
        return true;
    }

}
