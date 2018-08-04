<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.api.ApiReadAction");

/**
 * Description of ApiIndexAction
 *
 * @author kwlok
 */
class ApiIndexAction extends ApiReadAction 
{
    public $dataProvider;//array of models
    public $afterIndex;//controller callback if any
    /**
     * Run in "api" mode 
     */
    public function callApi() 
    {
        if (isset($_GET['page']))
            $this->queryParams = '?page='.$_GET['page'];
        $this->findAccessToken();
        $this->execCurl($this->getAuthBearerHeader());
    }
    
    public function onSuccess($response,$httpCode)
    {
        $items = [];
        if ($httpCode==200){
            $result = json_decode($response,true);
            foreach ($result['items'] as $item) {
                $this->setApiModel(new $this->model);
                if ($this->apiModel->hasAttribute('account_id'))
                    $this->apiModel->account_id = user()->getId();
                $this->refreshApiModel($item,$this->attributes,$this->childAttributes);
                $items[] = $this->apiModel;
                $this->resetApiModel();
            }
            logTrace(__METHOD__.' result _meta',$result['_meta']);
            $pagination = new CPagination($result['_meta']['totalCount']);
            $pagination->setCurrentPage($result['_meta']['currentPage']-1);
            $pagination->setPageSize($result['_meta']['perPage']);
            logTrace(__METHOD__.' result _links',$result['_links']);
            $links = $result['_links'];
        }
        
        if (isset($this->afterIndex))
            $this->controller->{$this->afterIndex}($items, $pagination, $links);
    }    
    /**
     * Refresh api models with response data
     * Mainly to get id to generate view url
     * @param type $item
     */
    protected function refreshApiModel($item,$attributes=[],$extraAttributes=[])
    {
        foreach ($item as $field => $value) {
            if (in_array($field,$attributes) || empty($attributes) || in_array($field,$extraAttributes))
                $this->apiModel->$field = $value;
        }
        //logTrace(__METHOD__.' '.$this->model.' refreshed',$this->apiModel->attributes);
    }    
}
