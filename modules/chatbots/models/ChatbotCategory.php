<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.ChatbotModel');
/**
 * Description of ChatbotCategory
 *
 * @author kwlok
 */
class ChatbotCategory extends ChatbotModel
{
    /**
     * @return The model class
     */
    public function getModelClass() 
    {
        return 'Category';
    }  
    /**
     * @return booolean check if has subcategories
     */
    public function getHasSubcategories()
    {
        return $this->model->hasSubcategories();
    }    
    /**
     * Get shop sub categories
     * @return array 
     */
    public function searchSubcategories($currentPage,$pageSize)
    {
        return $this->searchModelTemplate('ChatbotCategorySub', 'searchSubcategories', [null, $pageSize],$this->constructPagination($currentPage, $pageSize));
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
