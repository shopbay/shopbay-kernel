<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SearchControllerBehavior
 *
 * @author kwlok
 */
class SearchControllerBehavior extends CBehavior 
{
    /*
     * Elasticsearch access component
     */
    public $elasticsearch;
    /*
     * Search targets; Array of SearchModel
     */
    public $targets = [];
    /*
     * Search fields; Array of search fields, default to ['name']
     */
    public $searchFields = ['name'];
    /*
     * Search filter; Array of fiter, default to [] "empty array"
     */
    public $filter = [];
    /*
     * Search results view
     */
    public $searchResultView = 'common.modules.search.views.default._results';
    /*
     * Search item view container
     */
    public $searchItemViewContainer = 'common.modules.search.views.default._result';
    /*
     * Search item view (general)
     */
    public $searchItemView_general = 'common.modules.search.views.default._item';
    /*
     * Search item view (shop)
     */
    public $searchItemView_shop = 'common.modules.search.views.default._shop';
    /*
     * Search item view (product)
     */
    public $searchItemView_product = 'common.modules.search.views.default._product';
    /*
     * Search item view (tutorial)
     */
    public $searchItemView_tutorial = 'common.modules.search.views.default._tutorial';
    /*
     * Search item view (question)
     */
    public $searchItemView_question = 'common.modules.search.views.default._question';
    /*
     * Ajax update id for yiilistview pagination
     */
    public $ajaxUpdate = 'search_results';
    /*
     * Search placeholder for input box
     */
    public $placeholder;
    /*
     * Search javascript method call
     */
    public $onsearch = 'dosearch()';
    /*
     * Search input id
     */
    public $searchInput = 'q';
    /*
     * Show search bar on top of search results; Default to "true"
     */
    public $loadSearchbar = true;
    /*
     * Search pagination route; Default is "null' means follow existing route, else if contains a 'callback' will prefix shop url at pagination route
     */
    public $paginationRoute;
    /**
     * Parse query request
     * @param type $query
     * @param type $page
     * @return type
     */
    public function parseQuery($query,$page=null)
    {
        try { 
            $this->elasticsearch = Yii::app()->getModule('search')->getElasticSearch();
            $this->elasticsearch->setQueryString($query,$this->searchFields);
            $this->elasticsearch->setAndTermFilter($this->getSearchFilter());
            if (isset($page))
                $this->elasticsearch->setPageNum($page);
            
            $dataProvider = $this->elasticsearch->getArrayDataProvider($this->targets);
            $route = $this->getSearchPaginationRoute();
            if (isset($route)){
                $dataProvider->pagination = array(
                    'route'=>$route,
                );
            }
            
            return (object)array(
                'status'=>'success',
                'dataProvider'=>$dataProvider,
            );

        } catch (CException $e) {
            logError(__METHOD__.' '.$e->getMessage());
            return (object)array(
                'status'=>'failure',
                'error'=>$e->getMessage(),
            );
        }
    }
    /**
     * Get search result based on query response
     * @param type $response
     * @return type
     */
    public function getSearchResults($response)
    {
        return $response->status=='success'
                ?CHtml::tag('div',array('id'=>$this->ajaxUpdate),$this->getOwner()->renderPartial($this->searchResultView,array('dataProvider'=>$response->dataProvider),true))
                :CHtml::tag('div',array('class'=>'search-error'),$response->error);
    }
    /**
     * Decide which search item view to use
     */
    public function renderSearchItemView($data)
    {
        if ($data instanceof SearchShop){
            $this->getOwner()->renderPartial($this->searchItemView_shop,array('data'=>$data));
        }
        else if ($data instanceof SearchProduct){
            $this->getOwner()->renderPartial($this->searchItemView_product,array('data'=>$data));
        }
        else if ($data instanceof SearchTutorial){
            $this->getOwner()->renderPartial($this->searchItemView_tutorial,array('data'=>$data));
        }
        else if ($data instanceof SearchQuestion){
            $this->getOwner()->renderPartial($this->searchItemView_question,array('data'=>$data));
        }
        else
            $this->getOwner()->renderPartial($this->searchItemView_general,array('data'=>$data));
    }
    /**
     * Get item view file
     */
    public function getItemView() 
    {
        return $this->searchItemViewContainer;
    }
    /**
     * Get ajax update id
     */
    public function getAjaxUpdateId() 
    {
        return $this->ajaxUpdate;
    }
    /**
     * Get placeholder
     */
    public function getSearchPlaceholder()
    {
        return $this->placeholder;
    }
    /**
     * Get search filter
     */
    public function getSearchFilter()
    {
        if (isset($this->filter['callback']))
            return $this->getOwner()->{$this->filter['callback']}();
        else 
            return $this->filter;
    }
    /**
     * Get search pagination route (for pagination use)
     */
    public function getSearchPaginationRoute()
    {
        if (isset($this->paginationRoute['callback']))
            return $this->getOwner()->{$this->paginationRoute['callback']}();
        else 
            return $this->paginationRoute;//not changing anything
    }
    /**
     * Get onsearch javascript method call
     */
    public function getOnSearch()
    {
        if (isset($this->onsearch['callback']))
            return $this->getOwner()->{$this->onsearch['callback']}();
        else 
            return $this->onsearch; 
    }
    /**
     * Get search input id
     */
    public function getSearchInput()
    {
        return $this->searchInput; 
    }
    /*
     * Get empty response
     */
    public function getSearchEmptyResponse()
    {
        return (object)array('status'=>'success','dataProvider'=>null);
    }
    /**
     * Check if to show search bar
     */
    public function showSearchbar()
    {
        if (isset($this->loadSearchbar['callback']))
            return $this->getOwner()->{$this->loadSearchbar['callback']}();
        else 
            return $this->loadSearchbar;        
    }    
}
