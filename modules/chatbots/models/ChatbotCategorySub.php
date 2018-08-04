<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotCategorySub
 *
 * @author kwlok
 */
class ChatbotCategorySub extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'CategorySub';
    }   
    
    public function toKey()
    {
        return $this->model->toKey();
    }
    
}
