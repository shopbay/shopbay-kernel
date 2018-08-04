<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotPaymentMethod
 *
 * @author kwlok
 */
class ChatbotPaymentMethod extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'PaymentMethod';
    }
    /**
     * Get name
     * @param string $locale
     */
    public function getName($locale=null)
    {
        return $this->model->getMethodName($locale);
    }
    /**
     * Get payment method text
     * @param string $locale
     */
    public function getText($locale=null)
    {
        return strip_tags($this->model->getDescription($locale));
    }
}
