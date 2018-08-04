<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * ShopTrendsPageView shows the trends for the shop
 *
 * @author kwlok
 */
class ShopTrendsPageView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $bubbles = [];
        $bubbleName = Sii::t('sii','Trends');
        $bubbleTitle = Sii::t('sii','Choose a trend');

        $buttons = [];
        $recentlyTrends = [
            new TrendPayload(TrendPayload::RECENT_LIKES),
            new TrendPayload(TrendPayload::RECENT_PURCHASES),
            new TrendPayload(TrendPayload::RECENTLY_DISCUSSED),
        ];
        foreach ($recentlyTrends as $recentlyPayload) {
            $buttons[] = new PostbackButton($recentlyPayload->title, $recentlyPayload->toString());
        }
        $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, null, $buttons);
        
        $buttons = [];//reset buttons
        $mostTrends = [
            new TrendPayload(TrendPayload::MOST_LIKES),
            new TrendPayload(TrendPayload::MOST_PURCHASED),
            new TrendPayload(TrendPayload::MOST_DISCUSSED),
        ];
        foreach ($mostTrends as $mostTrends) {
            $buttons[] = new PostbackButton($mostTrends->title, $mostTrends->toString());
        }
        $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, null, $buttons);
        
        $this->sendGenericTemplate($this->context->sender,$bubbles);
    }
}
