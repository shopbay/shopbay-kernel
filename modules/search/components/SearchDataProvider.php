<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SearchDataProvider
 *
 * @author kwlok
 */
class SearchDataProvider extends CArrayDataProvider 
{
    /**
     * Fetches the data item keys from the persistent data storage.
     * @return array list of data item keys.
     */
    protected function fetchKeys()
    {
        if (empty($this->rawData))
            return array();
        return parent::fetchKeys();
    }    
    /**
     * Fetches the data from the persistent data storage.
     * @return array list of data items
     */
    protected function fetchData()
    {
        if(($sort=$this->getSort())!==false && ($order=$sort->getOrderBy())!='')
                $this->sortData($this->getSortDirections($order));

        if(($pagination=$this->getPagination())!==false)
        {
            //logTrace(__METHOD__.' inside pagination $this->rawData',$this->rawData);
            $pagination->setItemCount($this->getTotalItemCount());
            return empty($this->rawData)?array():$this->rawData;
        }
        else
            return empty($this->rawData)?array():$this->rawData;
    }
    
}
