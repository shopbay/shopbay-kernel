<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ProductShippingsView
 *
 * @author kwlok
 */
class ProductShippingsView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $product = new ChatbotProduct($payload->params['product_id']);
        $this->sendImage($this->context->sender, $product->imageUrl);

        $searchResult = $this->getSearchResult($product, 'searchShippings');
        //$bubbles = [];
        
        foreach ($searchResult as $shipping) {
            $this->sendTextMessage($this->context->sender,$shipping->getText($product->name));
//          $heading = Sii::t('sii','{shipping} for {product}',['{product}'=>$product->name,'{shipping}'=>$shipping->name]);
//            $buttons = [];//maximum 3 (but auto trimmed when limit exceeds)
//            $buttons[] = new WebUrlButton(Sii::t('sii','Open in browser'),$product->url);
//            //Put into $bubbles
//            $bubbles[] = new Bubble($heading, $shipping->text, $product->url, null, $buttons);
        }
//        $this->sendGenericTemplate($this->context->sender,$bubbles);
        
    }
}
