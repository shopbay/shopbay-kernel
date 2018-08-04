<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SearchableBehavior
 *
 * @author kwlok
 */
class SearchableBehavior extends CActiveRecordBehavior 
{
    /*
     * The search model
     */
    public $searchModel;
    /**
     * Save search index
     */
    public function saveSearchIndex() 
    {
        $this->loadElasticSearch();
        $searchModel = new $this->searchModel;
        $searchModel->assignAttributes($this->getOwner());
        try {
            if ($this->elasticsearch->exists($searchModel)){
                logTrace(__METHOD__.' '.get_class($searchModel).' found, updating...');
                $this->elasticsearch->update($searchModel);
            }
            else {
                logTrace(__METHOD__.' '.get_class($searchModel).' not found, insert new one...');
                $this->elasticsearch->insert($searchModel);
            }
            
        } catch (CException $e) {
            throw new CException('Could not resolve search host');
        }
    }
    /**
     * Delete search index
     */
    public function deleteSearchIndex() 
    {
        $this->loadElasticSearch();
        $searchModel = new $this->searchModel;
        $searchModel->assignAttributes($this->getOwner());
        $this->elasticsearch->delete($searchModel);
    }    
    /**
     * Get elasticsearch application component
     * Also, it loads required classes from SearchModules based on import settings
     */
    private $_es;
    protected function loadElasticSearch()
    {
        if (!isset($this->_es))
            $this->_es = Yii::app()->getModule('search')->getElasticSearch();
        return $this->_es;
    }
    protected function getElasticSearch()
    {
        return $this->loadElasticSearch();
    }
}
