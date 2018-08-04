<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ApiDataProvider
 *
 * @author kwlok
 */
class ApiDataProvider extends CArrayDataProvider 
{
    public $totalCount = 0;
    /**
     * Fetches the data from the persistent data storage.
     * @return array list of data items
     */
    protected function fetchData()
    {
        if(($sort=$this->getSort())!==false && ($order=$sort->getOrderBy())!='')
            $this->sortData($this->getSortDirections($order));

        if(($pagination=$this->getPagination())!==false){
            $pagination->setItemCount($this->getTotalItemCount());
        }

        return $this->rawData;
    }    
    /**
     * Calculates the total number of data items.
     * This method simply returns the number of elements in {@link rawData}.
     * @return integer the total number of data items.
     */
    protected function calculateTotalItemCount()
    {
        return $this->totalCount;
    }
}
