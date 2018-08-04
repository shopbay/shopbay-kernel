<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ElasticsSearch
 *
 * @author kwlok
 */
class ElasticSearch extends CApplicationComponent
{
    public $query;
    public $filter;
    public $pageSize = 5;//default to 5
    public $pageNum = 1;//default to 1 (first page)
    /**
     * Init
     */
    public function init()
    {
        parent::init();
        if (isYii1()){
            bootstrapYii2Engine();
            importYii2Extension('elasticsearch',[
                'ActiveRecord','ActiveQuery','Command','Connection',
                'DebugAction','DebugPanel','Exception','Query','QueryBuilder',
                //new Classes added support for 2.0.4 March 17, 2016
                'ActiveFixture','BatchQueryResult',
            ]);        
        }
    } 
    /**
     * Exists model
     * @param model $model single model
     * @return boolean
     */
    public function exists($model)
    {
        try {
            $this->setMatchQuery($model->primaryKey,'_id');
            $queryObj = $model::find()->from($model::index(),$model::type())->query($this->query);
            logTrace(__METHOD__.' query: '.json_encode($queryObj));
            return $queryObj->one()!=false; 
        } catch (Exception $ex) {
            logError(__METHOD__.' '.$ex->getMessage());
            throw new CException(Sii::t('sii','Search request failed. Please try again.'));
        }        
    }    
    /**
     * Save (Index) model
     * @param mixed $model Array of models or single model
     * @param boolean $refresh Indicate if to refresh (delete all then save)
     * @return type
     */
    public function save($model, $refresh=false)
    {
        if ($refresh){
            //[1] delete all first
            if (is_array($model)){
                foreach ($model as $_m) {
                    $this->deleteAll($_m);
                    break;
                }
            }
            else {
                $this->deleteAll($model);
            }
        }
        //save model
        $this->_crud($model, 'save');
    }
    /**
     * Insert model
     * @param mixed $model Array of models or single model
     * @return type
     */
    public function insert($model)
    {
        $this->_crud($model, 'insert');
    }    
    /**
     * Update model
     * @param mixed $model Array of models or single model
     * @return type
     */
    public function update($model)
    {
        $model->setAsOldRecord();
        $this->_crud($model, 'update');
    }    
    /**
     * Delete model
     * @param mixed $model Array of models or single model
     * @return type
     */
    public function delete($model)
    {
        $model->setAsOldRecord();
        $this->_crud($model, 'delete');
    }    
    /**
     * Deletes rows in the table using the provided conditions.
     * WARNING: If you do not specify any condition, this method will delete ALL rows in the table.
     *
     * For example, to delete all product whose status is "PRD;ON;":
     *
     * ~~~
     * Product::deleteAll('status = "PRD;ON;"');
     * ~~~
     * @see \yii\elasticsearch\ActiveRecord
     * 
     * @param array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
     * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
     * @return integer the number of rows deleted
     * @throws Exception on error.
     */
    public function deleteAll($model, $condition = [])
    {
        return $model::deleteAll($condition); 
    }    
    /**
     * Entry search method
     * @param mixed $query
     */
    public function search($model,$query=null)
    {
        if (!isset($query)){
            if ($this->query===null)
                throw new CException('Query not set');
            else
                $query = $this->query;
        }
        $queryObj = $model::find()->from($model::index(),$model::type())->query($query);
        
        if (isset($this->filter)){
            $queryObj = $queryObj->filter($this->filter);
        }
        if (isset($this->pageSize)){
            $queryObj = $queryObj->limit($this->pageSize);
        }
        if ($this->pageNum>1){
            $queryObj = $queryObj->offset($this->getOffset());
        }
        
        logTrace(__METHOD__.' query: '.json_encode($queryObj));
        
        try {
            return $queryObj->search(); 
        } catch (Exception $ex) {
            logError(__METHOD__.' '.$ex->getTraceAsString());
            throw new CException(Sii::t('sii','Search request failed. Please try again.'));
        }
    }
    /**
     * A helper method to search but return CArrayDataProvider
     * @param mixed $models Single model or array of search models
     * @param type $query
     * @return \CArrayDataProvider
     */
    public function getArrayDataProvider($models,$query=null,$pageSize=null)
    {
        $hits = array();
        $total = 0;
        if (is_array($models)){
            foreach ($models as $model) {
                $results = $this->search($model, $query);
                $hits = array_merge($hits, !empty($results['hits']['hits'])?$results['hits']['hits']:array());
                $total += $results['hits']['total'];
            }
        }
        else {
            $results = $this->search($models, $query);
            $hits = array_merge($hits, !empty($results['hits']['hits'])?$results['hits']['hits']:array());
            $total += $results['hits']['total'];
        }
        
        logTrace(__METHOD__.' search results',$hits);
        logTrace(__METHOD__." total = $total");
        
        if (!isset($pageSize))
            $pageSize = $this->pageSize;
        
        $dataProvider = new SearchDataProvider($hits,array(
                            'keyField'=>false,
                            //'sort'=>false,
                            'pagination'=>array('pageSize'=>$pageSize),
                        ));
        $dataProvider->setTotalItemCount($total);        
        $pager = $dataProvider->pagination;
        $pager->currentPage = $this->pageNum - 1;
        return $dataProvider;
    }
    /**
     * Get search offset based on current pageNum and pageSize
     * @return integer 
     */
    public function getOffset()
    {
        return ($this->pageNum - 1) * $this->pageSize;
    }
    /**
     * Set search page number
     * @param integer $pageNum
     */
    public function setPageNum($pageNum)
    {
        $this->pageNum = $pageNum;
    }
    /**
     * Set search page size (search limit)
     * @param integer $limit
     */
    public function setPageSize($limit)
    {
        $this->pageSize = $limit;
    }
    /**
     * Set match query (single field query)
     * 
     * @param string $searchText
     * @param string $field Default to field "name"
     * @return type
     */
    public function setMatchQuery($searchText,$field='name')
    {
        $this->query = [ "match" => [ $field => $searchText] ];
    }
    /**
     * Set multi match query (multiple fields query)
     * 
     * @param string $searchText
     * @param array $fields Default to field "name"
     * @return type
     */
    public function setMultiMatchQuery($searchText,$fields=['name'])
    {
        $this->query = [ "multi_match" => [ 
                    'query' => $searchText,
                    'fields'=>$fields,
                ]];
    }    
    /**
     * Set filtered query dsl
     * @param type $queryText
     * @param type $fields
     * @param type $term
     */
    public function setQueryString($queryText,$fields=[])
    {
        $this->query = ['query_string'=>[
                                    'query'=> $queryText.'*',
                                    'fields'=>$fields,
                                ]
                           ];
    }    
    /**
     * Set filtered query dsl
     * @param type $queryText
     * @param type $fields
     * @param type $term
     */
    public function setFilteredQuery($queryText,$fields=[],$term=[])
    {
        $this->query = ['filtered'=>[
                    'query'=>['query_string'=>[
                                    'query'=> $queryText.'*',
                                    'fields'=>$fields,
                                ]
                            ], 
                    'filter' =>[
                        'term'=>$term,
                    ],
                ]];
    }
    /**
     * Set and filter
     * @param array $terms Array of term filter
     */
    public function setAndTermFilter($terms=[])
    {
        $and = [];
        foreach ($terms as $field => $value) {
            $and[] = ['term'=>[$field=>$value]];
        }
        if (!empty($and))
            $this->filter = [ 'and'=> $and];
    }
    /**
     * Set term filter
     * @param array $term
     */
    public function setTermFilter($term=[])
    {
        $this->filter = [ 'term'=>$term ];
    }
    /**
     * Set fuzzy query
     * @param array $fields
     * @param string $likeText
     * @return type
     */
    public function setFuzzyQuery($likeText,$fields=['name'])
    {
        $this->query =  [ "fuzzy_like_this" => [
            "fields" => $fields,
            "like_text" => $likeText,
            "max_query_terms" => 12,
        ]];
    }
    /**
     * CRUD boilerplate 
     * @param mixed $model Array of models or single model
     * @return type
     */
    private function _crud($model,$action,$attributes=[])
    {
        if (is_array($model)){
            foreach ($model as $_m) {
                $this->{$action}($_m);
            }
        }
        else {
            try {
                $res = $model->{$action}();
                logTrace(__METHOD__." $action response = $res",$model->getAttributes());
            } catch (Exception $ex) {
                throw new CException(Sii::t('sii','Search request failed. Please try again.'));
            }            
        }
    }
    
}
