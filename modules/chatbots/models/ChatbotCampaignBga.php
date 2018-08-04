<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotCampaignBga
 *
 * @author kwlok
 */
class ChatbotCampaignBga extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'CampaignBga';
    }  
    /**
     * Check if campaign has image
     * @return type
     */
    public function getHasImage()
    {
        return $this->model->image!=null;
    }
    /**
     * Get model url
     * @return string
     */
    public function getUrl()
    {
        return $this->model->getUrl(true);
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
     * Get product x image url
     * @return type
     */
    public function getProductXImageUrl()
    {
        return $this->model->x_product->getImageOriginalUrl();
    } 
    /**
     * Get product y image url
     * @return type
     */
    public function getProductYImageUrl()
    {
        return $this->model->y_product->getImageOriginalUrl();
    } 
}
