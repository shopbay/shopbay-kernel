<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
Yii::import("common.modules.shops.components.ShopPage");
/**
 * ShopCategoriesView shows all categories owned by the shop
 *
 * @author kwlok
 */
class ShopCategoriesView extends MessengerView
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
        $bubbleName = Sii::t('sii','Categories');
        $bubbleTitle = Sii::t('sii','Choose a category');
        $buttons = [];

        $currentPage = $this->getCurrentPage($payload);
        
        $searchResult = $this->getSearchResult($model, 'searchCategories', [$currentPage]);
        foreach ($searchResult->data as $category) {
            if ($category->hasSubcategories)
                $categoryPayload = new ShopPayload(ShopPayload::CATEGORIES_SUB,['category_id'=>$category->id]);
            else
                $categoryPayload = new ShopPayload(ShopPage::CATEGORY,['category_id'=>$category->id]);

            $buttons[] = new PostbackButton($category->name, $categoryPayload->toString());
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
            $this->sendTextMessage($this->context->sender,Sii::t('sii','We are sorry that currently we are in process of setting up categories.'));
        }
    }
}
