<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This client serves as wrapper of elasticsearch-php provisioned by elasticsearch website
 * 
 * To use, set an application components at main.php
 * 
 *  //application components
 *  'components'=>array(
 *      ...
 * 
 *      'elasticsearch'=>array( 
 *          'class' => 'common.modules.search.components.ActiveESClient',
 *      ),
 *  )
 */
class ActiveESClient extends CApplicationComponent
{
    private $_client;

    public function init()
    {
       require_once(Yii::getPathOfAlias('common.vendors.elasticsearch-client-lib').'/autoload.php');
       $this->_client = new Elasticsearch\Client();
    }
    /*
     * prepareParams Example:
     * (1) Array          $models = Order::model()->recently(10)->findAll();
     * (2) Single record  $model = Order::model()->findByPk(316);
     */
    public function prepareParams($index,$type,$record){
        $params = array();
        $params['index'] = $index;
        $params['type']  = $type;
        if (is_array($record)){
            $body = '';
            foreach($record as $model){
                $body .= "{ \"index\" : {\"_id\":\"".$model->id."\"} }\n";
                $data = array_merge($model->getAttributes(array(
                                        'id',
                                        'account_id',
                                        'order_no',
                                        'item_total',
                                        'shipping_total',
                                        'grand_total',
                                        'status',
                                    )), 
                                    array(
                                        'status_text'=>Process::getDisplayText(Process::getText($model->status)),
                                        'purchase_time'=>date(DateTime::ISO8601,$model->create_time),
                          ));
                $body .= json_encode($data)."\n";
            }
            $params['body'] =  $body."\n";
        }
        else {
            $params['id']    = $record->id;
            $params['body']  = $record->getAttributes();
        }
        return $params;
    }

    public function index($params){
        return $this->_client->index($params);
    }
    
    public function bulk($params){
        return $this->_client->bulk($params);
    }
    
    public function create($index){
        $params = array();
        $params['index'] = $index;
        
        return $this->_client->indices()->create($params);
    }
    
    public function putMapping($index,$type){
        
        // Adding a new type to an existing index
        $mapping = array(
            'properties' => array(
//                'item_total' => array(
//                    'type' => 'float',
//                ),
//                'shipping_total' => array(
//                    'type' => 'float',
//                ),
//                'grand_total' => array(
//                    'type' => 'float',
//                ),
                'purchase_time' => array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'purchase_time' => array (
                            'type' => 'date',
                            'format' => 'date_optional_time',
                        ),
                        'text' => array (
                            'type' => 'string',
                            'index' => 'not_analyzed',
                        ),
                    )
                )
            )
        );        
        
        $params = array();
        $params['index'] = $index;
        $params['type']  = $type;
        $params['body'][$type] = $mapping;
        return $this->_client->indices()->putMapping($params);
    }

    public function delete($index){
        $params = array();
        $params['index'] = $index;
        return $this->_client->indices()->delete($params);
    }
    
    public function search($params){
        return $this->_client->search($params);
    }
    /*
     * searchByQuery Example:
     * (1) $query_string = array('query_string'=>array( 'query'  => 'product ABC', 'fields'=>array()));
     * (2) $filtered = array('filtered'=>array( 'query'  => $query_string, 
     *                                'filter' => array(
     *                                       'term'=>array('account_id'=>19))
     *                           ));
     */
    public function searchByQuery($index,$type,$query,$highlight=null){
        
        $params = array();
        $params['index'] = $index;
        $params['type'] = $type;
        $params['body']['query'] = $query;
        $params['body']['highlight'] = $highlight;
        Yii::log(json_encode($params),  CLogger::LEVEL_TRACE);
        return $this->_client->search($params);
    }
    /*
     * searchByJSON Example:
     * $json = '{
     *   "query" : {
     *       "match_all" : {}
     *       }
     *   }';
     */
    public function searchByJSON($index,$type,$json){
        $params = array();
        $params['index'] = $index;
        $params['type'] = $type;
        $params['body'] = $json;
        Yii::log(json_encode($params),  CLogger::LEVEL_TRACE);
        return $this->_client->search($params);
    }
    
    public function getAsActiveRecords($records){
        $results = new CList();
        foreach ($records['hits']['hits'] as $record) {
            $model = new $record['_type'];
            foreach ($record['_source'] as $key => $value) {
                //if (!($key!='status_text' || $key!='purchase_time' ))
               //     $model->$key = $value;
                if ($key!='id'){
                    $model = Order::model()->findByPk($value);
                    break;
                }
            }
            
            //$model->getRelated('items',true);
            $results->add($model);
        }
        return $results->toArray();
    }
 
    public function getAsArrayDataProvider($records){
        if ($records['hits']['total']>0)
            return new CArrayDataProvider(
                $this->getAsActiveRecords($records)
            );
        else
            return new CArrayDataProvider(array());
    }
    
    public function searchByContext($index,$type,$query,$userid){

        $query_string = array('query_string'=>array(
                            'query'=> $query.'*',
                            'fields'=>array(
                                'order_no',
                                'item_total',
                                'currency',
                                'shipping_total',
                                'grand_total',
                                'status_text',//todo
                                'purchase_time.text',
                                )
                        ));
        $filtered = array('filtered'=>array( 'query'=>$query_string, 
                                 'filter' => array(
                                        'term'=>array('account_id'=>$userid))
                            ));
        $highlight = array(
                            "pre_tags"=> array("<b>"),
                            "post_tags"=> array("</b>"),
                            "fields"=> array(
                              "order_no" => new stdClass(),//for empty object
                              "item_total" => new stdClass(),//for empty object
                              "grand_total" => new stdClass(),//for empty object
                              "status_text" => new stdClass(),//for empty object
                              "purchase_time.text" => new stdClass(),//for empty object
                            )
                    );


        return Yii::app()->elasticsearch->searchByQuery($index,$type,$filtered,$highlight);
    }

}