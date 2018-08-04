<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
/**
 * Description of ProductPayload
 *
 * @author kwlok
 */
class ProductPayload extends ChatbotPayload 
{
    CONST SHIPPINGS  = 'shippings';//product shipping information
    CONST OPTIONS    = 'options';//product options information
    /**
     * Type prefix
     * @return string
     */
    public function getTypePrefix()
    {
        return ChatbotPayload::PRODUCT;
    }    
}
