<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * ShopPaymentPageView shows the "payment" information for the shop
 *
 * @author kwlok
 */
class ShopPaymentPageView extends MessengerView
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
   
        if ($currentPage==0)
            $this->sendTextMessage($this->context->sender, Sii::t('sii','We accept:'));

        $searchResult = $this->getSearchResult($model, 'searchPaymentMethods', [$currentPage]);
        foreach ($searchResult->data as $index => $paymentMethod) {
            if ($searchResult->totalItemCount>0)
                $text = '('.($index+1).') '.$paymentMethod->name."\n\n";
            else
                $text = $paymentMethod->name."\n\n";
            $text .= $paymentMethod->text;
            $this->sendTextMessage($this->context->sender, $text);
        }
        $this->renderSummaryText($payload, $searchResult, $currentPage);
    }
}
