<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotNews
 *
 * @author kwlok
 */
class ChatbotNews extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'News';
    }  
    /**
     * Check if category has image
     * @return type
     */
    public function getHasImage()
    {
        return $this->model->hasImage();
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
     * Get model url
     * @return string
     */
    public function getContent($locale=null)
    {
        return strip_tags($this->model->getMarkdownContent($locale));
    }    
}
