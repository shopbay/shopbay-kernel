<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ReceiptView
 *
 * @author kwlok
 */
class ReceiptView extends MessengerView
{
    /**
     * Render view (send to messenger)
     * @param MessengerPayload $payload 
     */
    public function render($payload) 
    {
        $model = $this->getMessengerModel();
        $order = Order::model()->findByPk($payload->params['order_id']);
        if ($order==null)
            logError(__METHOD__.' Order not found!',$payload->params['order_id']);
        elseif ($order->shop_id!=$model->id)//shop id must match to have security check
            logError(__METHOD__." Order shop $order->shop_id not matching with chatbot shop $model->id");
        else{
            $this->sendTextMessage($this->context->sender,Sii::t('sii','Thanks for your purchase. Here is your receipt for the order {order_no}',['{order_no}'=>$order->order_no]));
            $this->sendReceiptTemplate($this->context->sender,$order);
        }
        /**
         * For subscription based notification, always return true sicne there is no error
         * @see Messenger::send()
         * @see SubscriberCommand::actionIndex()
         */
        return true;
        
    }
}
