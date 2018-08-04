<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
Yii::import("common.modules.shops.components.ShopPage");
/**
 * ShopCategoriesSubView shows all sub-categories of a particular category owned by the shop
 *
 * @author kwlok
 */
class ShopCategoriesSubView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $category = new ChatbotCategory($payload->params['category_id']);

        $bubbles = [];//maximum 10 (but auto trimmed when limit exceeds)
        $bubbleName = $category->name;
        $bubbleTitle = Sii::t('sii','Choose a sub-category');
        $bubbleImageUrl = $category->hasImage?$category->imageUrl:null;
        $buttons = [];
        
        $currentPage = $this->getCurrentPage($payload);
        
        $searchResult = $this->getSearchResult($category, 'searchSubcategories', [$currentPage]);
        foreach ($searchResult->data as $subcategory) {
            $subcategoryPayload = new ShopPayload(ShopPage::CATEGORY,['category_id'=>$category->id,'sub_category_id'=>$subcategory->id]);
            
            $buttons[] = new PostbackButton($subcategory->name, $subcategoryPayload->toString());
            //Put into $bubbles
            if (count($buttons)==Bubble::$buttonsLimit){
                $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, $bubbleImageUrl, $buttons);
                $buttons = [];//reset buttons
            }
        }
        //oustanding buttons
        if (!empty($buttons))
            $bubbles[] = new Bubble($bubbleName, $bubbleTitle, null, $bubbleImageUrl, $buttons);
        
        if (!empty($bubbles)){
            $this->sendGenericTemplate($this->context->sender,$bubbles);
            $this->renderSummaryText($payload, $searchResult, $currentPage);
        }
        else {
            $this->sendTextMessage($this->context->sender,Sii::t('sii','We are sorry that currently we are in process of setting up sub-categories.'));
        }
    }
}
