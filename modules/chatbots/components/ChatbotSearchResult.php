<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.search.components.*');
Yii::import('common.modules.search.models.*');
/**
 * Description of ChatbotSearchResult
 *
 * @author kwlok
 */
class ChatbotSearchResult extends CComponent
{
    /*
     * Returns the data items currently available. Array of ChatbotModel
     */
    public $data = [];
    /*
     * Returns the total number of data items.
     */
    public $totalItemCount;
    /*
     * Returns the number of data items in the current page.
     */
    public $itemCount;
    /*
     * Returns the pagination object.
     */
    public $pagination = false;
    /**
     * Get last page
     * @param type $currentPage
     * @return type
     */
    public function getLastPage()
    {
        return ceil($this->totalItemCount / $this->pagination->pageSize);
    }
    /**
     * Check if result has more page 
     * @param type $currentPage
     * @return boolean
     */
    public function hasMorePages($currentPage)
    {
        return $this->lastPage > ($currentPage+1);//zero-based page index
    }
    /**
     * Get remaining number of products based on current page
     * @param type $currentPage
     * @return boolean
     */
    public function getRemainingNumberOfProducts($currentPage)
    {
        return $this->totalItemCount - $this->pagination->pageSize * ($currentPage + 1);
    }
    /**
     * Get page start position 
     * @param type $currentPage
     * @return boolean
     */
    public function getPageStartPosition($currentPage)
    {
        if ($currentPage==0)
            return 1;
        else
            return $currentPage * $this->pagination->pageSize + 1;
    }
    /**
     * Get page end position 
     * @param type $currentPage
     * @return boolean
     */
    public function getPageEndPosition($currentPage)
    {
        return $this->getPageStartPosition($currentPage) + $this->itemCount - 1;
    }
}
