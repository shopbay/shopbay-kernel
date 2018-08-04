<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ProductOptionsView
 *
 * @author kwlok
 */
class ProductOptionsView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $product = new ChatbotProduct($payload->params['product_id']);
        $this->sendImage($this->context->sender, $product->imageUrl);

        $searchResult = $this->getSearchResult($product, 'searchAttributes',[50]);//select all options out, set page size to be 100 to be as high as possible; current option limit is only 6
        $text = Sii::t('sii','Options for {product}',['{product}'=>$product->name])."\n\n";
        foreach ($searchResult->data as $index => $productOption) {
            $text .= $productOption->text."\n";
        }
        
        if ($searchResult->totalItemCount==0)
            $this->sendTextMessage($this->context->sender,Sii::t('sii','{product} has no options.',['{product}'=>$product->name]));
        else{
            $buttons = [];
            $buttons[] = new WebUrlButton('View product',$product->url);
            $this->sendButtonTemplate($this->context->sender,$text,$buttons);
        }
    }
}
