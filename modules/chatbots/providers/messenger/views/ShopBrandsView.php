<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of class ShopBrandsView
 *
 * @author kwlok
 */
class ShopBrandsView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $model->setSearchMethod(ChatbotModel::DB_SEARCH);//todo use elasticsearch only when Chinese chars search is working

        $bubbles = [];//maximum 10 (but auto trimmed when limit exceeds)
        $bubbleName = Sii::t('sii','Brands');
        $bubbleTitle = Sii::t('sii','Choose a brand');
        $buttons = [];

        $currentPage = $this->getCurrentPage($payload);
        
        $searchResult = $this->getSearchResult($model, 'searchBrands', [$currentPage]);
        foreach ($searchResult->data as $brand) {
            $brandPayload = new ShopPayload(ShopPage::BRAND,['brand_id'=>$brand->id]);

            $buttons[] = new PostbackButton($brand->name, $brandPayload->toString());
            //Put into $bubbles
            if (count($buttons)==Bubble::$buttonsLimit){
                $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, null, $buttons);
                $buttons = [];//reset buttons
            }
        }
        //oustanding buttons
        if (!empty($buttons))
            $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, null, $buttons);
        
        if (!empty($bubbles)){
            $this->sendGenericTemplate($this->context->sender,$bubbles);
            $this->renderSummaryText($payload, $searchResult, $currentPage);
        }
        else {
            $this->sendTextMessage($this->context->sender,Sii::t('sii','We are sorry that currently we are in process of setting up brands.'));
        }
    }
}
