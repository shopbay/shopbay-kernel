<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * ShopPromotionsPageView shows the shop promotion information
 *
 * @author kwlok
 */
class ShopPromotionsPageView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $model->setSearchMethod(ChatbotModel::DB_SEARCH);//todo use elasticsearch only when Chinese chars search is working

        if ($model->hasCampaignSale){
            $this->sendTextMessage($this->context->sender, $model->campaignSale->text);
        }
        
        $currentPage = $this->getCurrentPage($payload);
        $searchResult = $this->getSearchResult($model, 'searchCampaignBgas', [$currentPage]);
        $bubbles = $this->renderBubbles($searchResult, $payload);
        
        if (!empty($bubbles)){
            $this->sendTextMessage($this->context->sender, Sii::t('sii','Check out our latest promotions.'));
            $this->sendGenericTemplate($this->context->sender,$bubbles);
            $this->renderSummaryText($payload, $searchResult, $currentPage);
        }
        else {
            $this->sendTextMessage($this->context->sender, Sii::t('sii','Currently we don\'s have any promotions. You may want to try other key words for search.'));
        }
        
    }
    /**
     * OVERRIDDEN METHOD
     */
    protected function renderBubble($payload,$campaign)
    {
        $buttons = [];//maximum 3 (but auto trimmed when limit exceeds)
        //1
        $buttons[] = new WebUrlButton(Sii::t('sii','View promotion'),$campaign->url);
        //Put into $bubbles
        $imageUrl = $campaign->hasImage?$campaign->imageUrl:$campaign->productXImageUrl;//todo to combine both product x and y as one image (with wording?)
        return new Bubble($campaign->campaignText, $campaign->validityText, $campaign->url, $imageUrl, $buttons);
    }     
}
