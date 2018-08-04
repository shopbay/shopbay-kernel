<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ShippingNoticeItemView
 *
 * @author kwlok
 */
class ShippingNoticeItemView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param MessengerPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $item = Item::model()->findByPk($payload->params['item_id']);
        if ($item==null)
            logError(__METHOD__.' Item not found!',$payload->params['item_id']);
        elseif ($item->shop_id!=$model->id)//shop id must match to have security check
            logError(__METHOD__." Item shop $item->shop_id not matching with chatbot shop $model->id");
        else{
            $buttons = [];//one button
            $buttons[] = new WebUrlButton(Sii::t('sii','View item'), Item::getAccessUrl($item,$model->domain));
            $subtitle = Sii::t('sii','Item {name} has been shipped',[
                    '{name}'=>$item->displayLanguageValue('name',null),//todo to implement locale
                ]);
            if ($item->order->hasAddress())
                $subtitle .= Sii::t('sii',' to {address}',['{address'=>$item->order->address->longAddress]);
            $bubbles = [new Bubble(Sii::t('sii','Shipping Notice'), $subtitle, Item::getAccessUrl($item), $item->productImageUrl, $buttons)];
            $this->sendGenericTemplate($this->context->sender,$bubbles);
        }
        /**
         * For subscription based notification, always return true sicne there is no error
         * @see Messenger::send()
         * @see SubscriberCommand::actionIndex()
         */
        return true;
    }

}
