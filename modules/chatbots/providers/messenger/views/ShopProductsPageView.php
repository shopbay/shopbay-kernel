<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ShopProductsPageView
 *
 * @author kwlok
 */
class ShopProductsPageView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $model->setSearchMethod(ChatbotModel::DB_SEARCH);//todo use elasticsearch only when Chinese chars search is working

        $query = null;
        if (isset($payload->params['query']))
            $query = $payload->params['query'];
        
        $currentPage = $this->getCurrentPage($payload);
        $searchResult = $this->getSearchResult($model, 'searchProducts', [$query,$currentPage]);
        $bubbles = $this->renderBubbles($searchResult, $payload);
        
        if (!empty($bubbles)){
            $this->sendGenericTemplate($this->context->sender,$bubbles);
            $this->renderSummaryText(new ShopPayload(ShopPayload::PRODUCTS,['query'=>$query]), $searchResult, $currentPage);
        }
        else {
            $this->sendTextMessage($this->context->sender, Sii::t('sii','We are sorry that we cannot find products related to {query}. You may want to try other key words for search.',['{query}'=>$query]));
            $searchResult = $this->getSearchResult($model, 'searchProducts', [null,$currentPage]);
            $bubbles = $this->renderBubbles($searchResult, null);//search all
            if (!empty($bubbles)){
                $this->sendTextMessage($this->context->sender, Sii::t('sii','You may find below items interesting.'));
                $this->sendGenericTemplate($this->context->sender,$bubbles);
            }
        }
    }
    /**
     * Get bubble
     * @param type $payload
     * @param type $product
     * @return \PostbackButton
     */
    protected function renderBubble($payload,$product)
    {
        $buttons = [];//maximum 3 (but auto trimmed when limit exceeds)
        //1
        $buttons[] = new WebUrlButton(Sii::t('sii','View product'),$product->url);
        //2
        $optionsPayload = new ProductPayload(ProductPayload::OPTIONS,['product_id'=>$product->id]);
        $buttons[] = new PostbackButton(Sii::t('sii','View options'), $optionsPayload->toString());
        //3
        $shippingsPayload = new ProductPayload(ProductPayload::SHIPPINGS,['product_id'=>$product->id]);
        $buttons[] = new PostbackButton(Sii::t('sii','View shipping info'), $shippingsPayload->toString());
        //Todo show recommendation or similar - can be same category or etc
//        $categoriesPayload = new ShopPayload(ShopPayload::CATEGORIES,['product_id'=>$product->id]);
//        $buttons[] = new PostbackButton(Sii::t('sii','Show me similar'), $categoriesPayload->toString());
        //Put into $bubbles
        return new Bubble($product->name, $product->price, $product->url, $product->imageUrl, $buttons);
    }

}
