<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.ShopProductsPageView');
/**
 * ShopCategoryPageView shows all products by the category
 *
 * @author kwlok
 */
class ShopCategoryPageView extends ShopProductsPageView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $model->setSearchMethod(ChatbotModel::DB_SEARCH);//todo use elasticsearch only when Chinese chars search is working

        $categoryKey = $payload->params['category_id'];
        if (isset($payload->params['sub_category_id'])){
            $categoryKey =  (new ChatbotCategorySub($payload->params['sub_category_id']))->toKey();
        }
        
        $currentPage = $this->getCurrentPage($payload);
        $searchResult = $this->getSearchResult($model, 'searchProductsByCategory', [$categoryKey,$currentPage]);
        $bubbles = $this->renderBubbles($searchResult, $payload);         
        
        if (!empty($bubbles)){
            $this->sendGenericTemplate($this->context->sender,$bubbles);
            $this->renderSummaryText($payload, $searchResult, $currentPage);
        }
        else {
            $this->sendTextMessage($this->context->sender, Sii::t('sii','We are sorry that we cannot find products related to category. You may want to try other key words for search.'));
        }
    }
}
