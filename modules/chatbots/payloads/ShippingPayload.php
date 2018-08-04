<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
/**
 * Description of ShippingPayload
 *
 * @author kwlok
 */
class ShippingPayload extends ChatbotPayload 
{
    CONST NOTICE_ITEM  = 'notice_item';//shipping notice per item
    /**
     * Type prefix
     * @return string
     */
    public function getTypePrefix()
    {
        return ChatbotPayload::SHIPPING;
    }    
}
