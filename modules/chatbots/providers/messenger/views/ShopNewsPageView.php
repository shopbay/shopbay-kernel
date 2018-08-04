<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ShopNewsPageView
 * 
 * @author kwlok
 */
class ShopNewsPageView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $model->setSearchMethod(ChatbotModel::DB_SEARCH);//todo use elasticsearch only when Chinese chars search is working
        
        $currentPage = $this->getCurrentPage($payload);
        $searchResult = $this->getSearchResult($model, 'searchNews', [$currentPage]);
        $bubbles = $this->renderBubbles($searchResult, $payload);
        
        if (!empty($bubbles)){
            $this->sendGenericTemplate($this->context->sender,$bubbles);
            $this->renderSummaryText(new ShopPayload(ShopPage::NEWS), $searchResult, $currentPage);
        }
        else {
            $this->sendTextMessage($this->context->sender, Sii::t('sii','We have no news for today. Do follow us to get latest update.'));
        }
    }
    /**
     * OVERRIDDEN METHOD
     */
    protected function renderBubble($payload,$news)
    {
        $buttons = [];//maximum 3 (but auto trimmed when limit exceeds)
        //1
        $buttons[] = new WebUrlButton(Sii::t('sii','Open in browser'),$news->url);
        //2
        $newsArticlePayload = new ShopPayload(ShopPayload::NEWS_ARTICLE,['news_id'=>$news->id]);
        $buttons[] = new PostbackButton(Sii::t('sii','Read it here'), $newsArticlePayload->toString());
        //Put into $bubbles
        return new Bubble($news->name, $news->content, $news->url, $news->imageUrl, $buttons);
    }    
}
