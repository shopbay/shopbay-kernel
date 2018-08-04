<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotBrand
 *
 * @author kwlok
 */
class ChatbotBrand extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'Brand';
    }  
    /**
     * Check if category has image
     * @return type
     */
    public function getHasImage()
    {
        return $this->model->hasImage();
    }
}
