<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.search.components.*');
Yii::import('common.modules.search.models.*');
/**
 * Description of ChatbotSearch
 *
 * @author kwlok
 */
class ChatbotSearch 
{
    /*
     * The elasticsearch object
     */
    protected $elasticsearch;
    /*
     * Search targets; Array of SearchModel
     */
    public $targets = [];
    /*
     * Search fields; Array of search fields, default to ['name']
     */
    public $fields = ['name'];
    /*
     * Search filter; Array of fiter, default to active object only
     */
    public $filter = ['status'=>SearchFilter::ACTIVE];
    /**
     * Constructor
     * @param $targets The target search model; Either array or single model value
     */
    public function __construct($targets)
    {
        //load ElasticSearch component from SearchModule
        $this->elasticsearch = Yii::app()->getModule('search')->getElasticSearch();
        if (is_array($targets))
            $this->targets = $targets;
        else 
            $this->targets[] = $targets;
    }
    /**
     * Add search filter
     * @param type $key
     * @param type $value
     */
    public function addFilter($key, $value)
    {
        $this->filter[$key] = $value;
    }
    /**
     * Search query 
     * @param type $query
     * @param type $page The target page
     * @return type
     */
    public function search($query,$page=null,$pageSize=null)
    {
        try { 
            
            $this->elasticsearch->setQueryString($query,$this->fields);
            $this->elasticsearch->setAndTermFilter($this->filter);
            if (isset($page))
                $this->elasticsearch->setPageNum($page);
            
            $dataProvider = $this->elasticsearch->getArrayDataProvider($this->targets,null,$pageSize);
            
            return $dataProvider;

        } catch (CException $e) {
            logError(__METHOD__.' '.$e->getMessage());
            return null;
        }
    }       
}
