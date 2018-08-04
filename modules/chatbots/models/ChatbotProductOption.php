<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotProductOption
 *
 * @author kwlok
 */
class ChatbotProductOption extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'ProductAttribute';
    }
    /**
     * Get product 
     * @return string
     */
    public function getProduct()
    {
        $product = new ChatbotProduct();
        $product->setModel($this->model->product);
        return $product;
    }    
    /**
     * Get option variations
     * @return string
     */
    public function getText($locale=null)
    {
        $text = $this->getName($locale)." - ";
        foreach ($this->model->searchOptions()->data as $data) {
            $text .= $data->displayLanguageValue('name',$locale);
            $text .= $data->getSurchargeText();
            $text .= ', ';
        }
        return rtrim($text,', ');//trim off last entry of ", "
    }    
}
