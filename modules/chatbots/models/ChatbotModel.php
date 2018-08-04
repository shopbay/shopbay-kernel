<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.components.ChatbotSearchResult');
/**
 * Chatbot Model suitable and easier for display in chatbot
 *
 * @author kwlok
 */
abstract class ChatbotModel extends CComponent
{
    CONST ELASTIC_SEARCH = 'elasticsearch';
    CONST DB_SEARCH      = 'dbsearch';
    /**
     * Default search method
     * @var type 
     */
    protected $searchMethod = self::ELASTIC_SEARCH;
    /*
     * Model id
     */
    private $_id;
    /*
     * Model instance
     */
    private $_m;
    /**
     * Constructor
     * @param type $id
     */    
    public function __construct($id=null) 
    {
        $this->_id = $id;
    }   
    /**
     * Set the search method
     * @param type $method The search method
     */
    public function setSearchMethod($method)
    {
        $this->searchMethod = $method;
    }    
    /**
     * @return The model class
     */
    abstract function getModelClass();
    /**
     * Return model instance
     * @return type
     */
    public function getModel()
    {
        if (!isset($this->_m) && isset($this->_id)){
            $modelClass = $this->getModelClass();
            $this->_m = $modelClass::model()->findByPk($this->_id);
        }
        return $this->_m;
    }
    /**
     * Set model
     * @param type $model The model instance
     */
    public function setModel($model)
    {
        $this->_m = $model;
        $this->_id = $model->id;
    }
    /**
     * Get model id
     * @return type
     */
    public function getId()
    {
        return $this->_id;
    }
    /**
     * Get model name
     * @return type
     */
    public function getName($locale=null)
    {
        return $this->model->displayLanguageValue('name',$locale);
    }    
    /**
     * Get model creation time
     * @return type
     */
    public function getCreationTime()
    {
        return $this->model->formatDatetime($this->model->create_time);
    }    
    /**
     * Get model url
     * @return string
     */
    public function getUrl()
    {
        return $this->model->getUrl(request()->isSecureConnection);
    }    
    /**
     * Get model url
     * @return string
     */
    public function getImageUrl()
    {
        return $this->model->getImageOriginalUrl();
    }      
    /**
     * Get any charge value for a field
     * @return boolean $currency If to include currency
     */
    public function getChargeValue($value,$currency=true)
    {
        if ($currency)
            return $this->model->formatCurrency($value);
        else
            return $value;
    }    
    /**
     * A boilerplate for model search, and convert into chatbot model
     * @param type $modelClass
     * @param type $searchModelMethod
     * @param array $searchParams 
     * @return \modelClass
     */
    protected function searchModelTemplate($modelClass,$searchModelMethod,$searchParams,$pagination=[])
    {
        $models = [];
        $dataProvider = call_user_func_array([$this->model, $searchModelMethod], $searchParams);
        
        if (isset($pagination['currentPage']))
            $dataProvider->pagination->currentPage = $pagination['currentPage'];
        if (isset($pagination['pageSize']))
            $dataProvider->pagination->pageSize = $pagination['pageSize'];
        
        foreach ($dataProvider->getData(true) as $data) {
            $model = new $modelClass();
            $model->setModel($data);
            $models[] = $model;
        }
        
        $result = new ChatbotSearchResult();
        $result->data = $models;
        $result->totalItemCount = $dataProvider->totalItemCount;
        $result->itemCount = $dataProvider->itemCount;
        $result->pagination = $dataProvider->pagination;
        return $result;
    }
    /**
     * Construct pagination
     * @param type $currentPage
     * @param type $pageSize
     * @return type
     */
    protected function constructPagination($currentPage,$pageSize)
    {
        return [
            'pageSize'=>$pageSize,
            'currentPage'=>$currentPage,
        ];
    }
    /**
     * Render markdown content
     * @param type $content
     * @return type
     */
    protected function renderMarkdownContent($content,$stripTags=true) 
    {
        $md = new CMarkdown();
        $content = $md->transform(Helper::purify($content));
        if ($stripTags)
            return strip_tags(Helper::addNofollow($content));
        else
            return Helper::addNofollow($content);
    }    
}
