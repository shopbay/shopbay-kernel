<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SPageIndexAction
 *
 * @author kwlok
 */
class SPageIndexAction extends CAction 
{
    /**
     * The page index view file
     * @var string 
     */
    public $index = 'undefined';
    /**
     * Model class of the page
     * @var type 
     */
    public $model = 'undefined';
    /**
     * The route to forward when scope is clicked
     * @see SPageIndex
     * @var type 
     */
    public $route = 'undefined';
    /**
     * Name of Page index
     * @var type 
     */
    public $viewName;
    /**
     * The page heading (page title) - To avoid the same name as controller->pageTitle (getPageTitle)
     * @var string 
     */
    public $pageHeading;
    /**
     * The page view; Default to "SPageIndex::VIEW_LIST"
     * @var string 
     */
    public $pageViewOption = SPageIndex::VIEW_LIST;
    /**
     * The page control (optional)
     * @var string 
     */
    public $pageControl;
    /**
     * Enable search capability in the page (optional)
     * @var string 
     */
    public $enableSearch = true;
    /**
     * Enable view options in the page (optional)
     * @var string 
     */
    public $enableViewOptions = true;
    /**
     * Default scope for search
     * @var string 
     */
    public $defaultScope = 'all';
    /**
     * Page control callback
     * @var string 
     */
    public $controlCallback = array();
    /**
     * Indicate if to use custom widget view; Default to 'false'
     * 
     * @var boolean 
     */
    public $customWidgetView = false;    
    /**
     * Indicate the current user locale; Default to null
     * 
     * @var boolean 
     */
    public $locale;    
    /**
     * Handles all input requests
     * @param query params
     */
    public function run() 
    {
        $this->controller->setPageTitle($this->pageHeading);
        
        logTrace(__METHOD__.' $_GET', $_GET);
        $this->controller->setPageView(isset($_GET['option'])?$_GET['option']:$this->pageViewOption);//default view option
        $this->controller->setScope(isset($_GET['scope'])?$_GET['scope']:$this->defaultScope);
        $config = $this->getConfig($this->controller->getScope());
        
        if (request()->getIsAjaxRequest())//mainly for pagingation and filtering via javascript
            $this->getWidgetJsonView($config);
        else
            $this->controller->render($this->index,array('config'=>$config));
    }  
    /**
     * Get widget view in json response 
     * @param type $config
     */
    protected function getWidgetJsonView($config)
    {
        header('Content-type: application/json');
        $config = array_merge($config,array('description'=>$this->getPageDescription()));
        $container = $this->controller->spageindexWidget($config, true);
        
        if (isset($_GET['ajax'])){//special handle for default yii gridview/listview pagination
            echo CJSON::encode($container->widget);
        }
        else {
            $json = ['widget'=>$container->widget];
            if ($this->enableSearch){
                $json['pagefilter'] = $this->controller->spagefilterWidget([],true);
            }
            echo CJSON::encode($json);
        }
        Yii::app()->end();
    }  
    
    protected function getConfig($scope)
    {
        $config = [
            'scope'=>$scope,
            'locale'=>$this->locale,
            'route'=>$this->route,
            'model'=>$this->model,
            'viewName'=>$this->viewName,
            'view'=>$this->controller->getPageView(),
            'enableViewOptions'=>false,//always false; viewOptions control is now handled by SPageIndexAction instead
            //'enableViewOptions'=>$this->enableViewOptions,
        ];
        
        if (isset($this->pageControl)){
            $config = array_merge(array('control'=>$this->pageControl),
                                  array('controlCallback'=>$this->controlCallback),
                                  array('filters'=>$this->controller->getScopeFilters()),
                                  $config);
        }
        if ($this->controller->getScopeDescription($scope)!=null){
            $config = array_merge(array('description'=>$this->controller->getScopeDescription($scope)),
                                  $config);
        }

        if ($this->enableSearch && $this->searchModel!=null){
            $this->searchModel->getDbCriteria()->mergeWith($this->controller->getSearchCriteria($this->searchModel));
        }

        $config = array_merge(array('widget'=>$this->getWidget($this->controller->getPageView(), $scope, $this->searchModel)),$config);

        return $config;
    }    
    /**
     * Get widget
     * 
     * Hack note: If were to define own widget, at controller return $dataProvider null
     * 
     * @param type $view
     * @param type $scope
     * @param type $searchModel
     * @return type
     * @throws CHttpException
     */
    protected function getWidget($view,$scope,$searchModel=null)
    {
        if ($this->customWidgetView){
            return $this->controller->getWidgetView($view,$scope,$searchModel);
        }        
        else {
            switch ($view) {
                case SPageIndex::VIEW_LIST:
                    return $this->controller->widget($this->controller->getModule()->getClass('listview'), 
                            array(
                               'id'=>$scope,
                               'dataProvider'=>$this->controller->getDataProvider($scope, $searchModel),
                               'viewOptionRoute'=>$this->enableViewOptions?$this->route:null,
                               'itemView'=>$this->controller->getWidgetView($view),
                               'afterAjaxUpdate'=>'function(id, data){ listviewupdate(); }',
                               'htmlOptions'=>array('data-description'=>$this->getPageDescription(),'data-view-option'=>$view),
                            ),
                            true);
                case SPageIndex::VIEW_GRID:
                    return $this->controller->renderPartial($this->controller->getWidgetView($view),array(
                                    'scope'=>$scope,
                                    'viewOption'=>$view,
                                    'searchModel'=>$searchModel,
                                    'viewOptionRoute'=>$this->enableViewOptions?$this->route:null,
                                    'pageDescription'=>$this->getPageDescription(),
                                ), true);
                default:
                    throw new CHttpException(400,Sii::t('sii','Unknown widget'));
            }
        }
    }

    protected function getPageDescription() 
    {
        return $this->controller->getScopeDescription($this->controller->getScope());
    }
    /**
     * Setup search model based on url params input
     * Support two ways:
     * [1] via array data struture Model[field], e.g. Order[order_no]=xxx 
     * [2] via direct attribute, e.g. order_no=xxx
     * @see $this->$searchMap
     * @return type
     */
    protected function getSearchModel()
    {
        if (!isset($this->controller->searchModel)){
            $this->controller->searchModel = isset($this->model) && $this->model!='undefined'? new $this->model() : null;
            if ($this->controller->searchModel!=null){
                $this->controller->searchModel->unsetAttributes();//clear all attributes first before assign
                foreach ($this->controller->searchMap as $searchField => $modelAttribute) {
                    if (isset($_GET[$this->model][$searchField]) && !empty($_GET[$this->model][$searchField]))
                        $this->controller->searchModel->$modelAttribute = urldecode($_GET[$this->model][$searchField]);
                    elseif (isset($_GET[$searchField]) && !empty($_GET[$searchField]))
                        $this->controller->searchModel->$modelAttribute = urldecode($_GET[$searchField]);
                }
                logTrace(__METHOD__.' '.$this->model.' attributes', $this->controller->searchModel->attributes);
            }
        }
        return $this->controller->searchModel;
    }
}
