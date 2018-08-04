<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotCampaignSale
 *
 * @author kwlok
 */
class ChatbotCampaignSale extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'CampaignSale';
    }  
    /**
     * Get campaign text
     * @return type
     */
    public function getCampaignText($locale=null)
    {
        return $this->model->getCampaignText($locale);
    }
    /**
     * Get campaign validity text
     * @return type
     */
    public function getValidityText()
    {
        return $this->model->getValidityText();
    } 
    /**
     * Get text
     * @return type
     */
    public function getText()
    {
        $text = $this->campaignText;
        $text .= '. '.$this->validityText;
        return $text;
    } 
}
