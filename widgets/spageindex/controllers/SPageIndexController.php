<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.spageindex.SPageIndex");
Yii::import("common.widgets.spageindex.actions.SPageIndexAction");
Yii::import('common.widgets.spagefilter.controllers.SPageFilterControllerTrait');
/**
 * Description of SPageIndexController
 *
 * @author kwlok
 */
abstract class SPageIndexController extends AuthenticatedController 
{
    use SPageFilterControllerTrait;
    /**
     * Inherited variable
     * @see SPageIndex
     * @var string
     */
    public $index = 'index';
    /**
     * Inherited variable
     * @see SPageIndex
     * @var string
     */
    public $modelType = 'undefined';
    /**
     * Inherited variable
     * @see SPageIndex
     * @var string
     */
    public $modelFilter = 'mine';
    /**
     * Inherited variable
     * @see SPageIndex
     * @var string 
     */
    public $route = 'undefined';
    /**
     * Name of page (also will be used as page title)
     * @see SPageIndex
     * @var string 
     */
    public $viewName;
    /**
     * Page heading (Page title) - If set, will override view name as page title
     * @see SPageIndex
     * @var string 
     */
    public $pageHeading;
    /**
     * Inherited variable
     * @see SPageIndex
     * @var string 
     */
    public $pageControl;
    /**
     * Inherited variable
     * The page view; Default to "SPageIndex::VIEW_LIST"
     * @see SPageIndex
     * @var string 
     */
    public $pageViewOption = SPageIndex::VIEW_LIST;
    /**
     * Inherited variable
     * @see SPageIndex
     * @var string 
     */
    public $enableViewOptions = true;
    /**
     * Inherited variable
     * @see SPageIndex
     * @var string 
     */
    public $enableSearch = true;
    /**
     * Model attribute for sorting
     * @var string 
     */
    public $sortAttribute = 'create_time';
    /**
     * Default scope for search
     * @var string 
     */
    public $defaultScope = 'all';
    /**
     * Page control callback
     * @var string 
     */
    public $controlCallback = [];
    /**
     * Indicate if to use custom widget view; Default to 'false'
     * When true, controller::getDataProvider() will not be called (or used)
     * 
     * @var boolean 
     */
    public $customWidgetView = false;
    /**
     * A search model to support page filtering
     */
    public $searchModel;
    /**
     * A field mapping for search model
     * array(
     *    <search_field> => <actual_model_attribute>
     *    ...
     * )
     */
    public $searchMap = [];
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return [
            'index'=>[
                'class'=>'SPageIndexAction',
                'model'=>$this->modelType,
                'route'=>$this->route,
                'viewName'=>$this->viewName,
                'pageHeading'=>isset($this->pageHeading)?$this->pageHeading:$this->viewName,//use view name as page heading (page title)
                'pageControl'=>$this->pageControl,
                'pageViewOption'=>$this->pageViewOption,
                'enableSearch'=>$this->enableSearch,
                'enableViewOptions'=>$this->enableViewOptions,
                'defaultScope'=>$this->defaultScope,
                'controlCallback'=>$this->controlCallback,
                'customWidgetView'=>$this->customWidgetView,
                'index'=>$this->index,
                'locale'=>user()->getLocale(),
            ],
        ];
    }      
    /**
     * Return the data provider based on scope and searchModel
     * 
     * If $customWidgetView is true, this method will return null (no data provider) so that controller to use custom widget view
     * @see SPageIndexAction::getWidget()
     * 
     * @return mixed CActiveDataProvider or null
     */
    public function getDataProvider($scope,$searchModel=null)
    {
        $type = $this->modelType;
        $type::model()->resetScope();
        $finder = $type::model()->{$this->modelFilter}()->{$scope}();
        if ($searchModel!=null)
            $finder->getDbCriteria()->mergeWith($searchModel->getDbCriteria());
        logTrace(__METHOD__.' '.$type.'->'.$this->modelFilter.'()->'.$scope.'()',$finder->getDbCriteria());
        return new CActiveDataProvider($finder, [
            'criteria'=>[
                'order'=>$this->sortAttribute.' DESC',
            ],
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
            'sort'=>false,
        ]);
    }        
    /**
     * Specify the search criteria for a model search
     * 
     * @see SPageIndex
     * @return CDbCriteria
     */
    public function getSearchCriteria($model)
    {
        return new CDbCriteria();
    }
    /**
     * Return the array of scope description
     * 
     * @see SPageIndexAction
     * @return array
     */
    public function getScopeDescription($scope)
    {
        return null;
    }    
    /**
     * Return the array of filters
     * 
     * @see SPageIndexAction
     * @return array
     */
    public function getScopeFilters()
    {
        //give default filter
        $filters = new CMap();
        $filters->add('all',Helper::htmlIndexFilter(['code'=>'all','text'=>Sii::t('sii','All')],true,false));
        return $filters->toArray();
//        throw new CException(Sii::t('sii','Please define scope filter'));
    }
    
    public function existsScopeFilter($scope)
    {
        $exists = false;
        foreach ($this->getScopeFilters() as $key => $value) {
            if ($key==$scope){
                $exists = true;
                break;
            }
        }
        return $exists;
    }
    public function existsViewOption($view)
    {
        return $view==SPageIndex::VIEW_LIST || $view==SPageIndex::VIEW_GRID;
    }
    
    public function getWidgetView($view,$scope=null,$searchModel=null)
    {
        if (!$this->customWidgetView)
            return '_'.strtolower($this->modelType).$view;
        else 
            throw new CException(Sii::t('sii','Please define widget view'));
    }    
    /**
     * session state variable
     */
    private $_stateVariableScope = '_session_spageindexscope';   
    private $_stateVariableView = '_session_spageindexview';   
    public function getScope()
    {
        return SActiveSession::get($this->_stateVariableScope);
    }    
    public function setScope($scope)
    {
        if (!$this->existsScopeFilter($scope)){
            $scope = $this->defaultScope;//use default scope
            //throw new CHttpException(400,Sii::t('sii','Scope not found'));
        }
        SActiveSession::set($this->_stateVariableScope,$scope);
    }    
    public function getPageView()
    {
        return SActiveSession::get($this->_stateVariableView);
    }    
    public function setPageView($view)
    {
        if (!$this->existsViewOption($view)){
            $view = $this->pageViewOption;//use default view
            //throw new CHttpException(400,Sii::t('sii','View option not found'));
        }
        SActiveSession::set($this->_stateVariableView,$view);
    }    

    public function getPageMenuCssClass($scope)
    {
        return 'pageindex-page-menu '.$scope.' '.($this->getScope()==$scope?'active':'');
    }
    /**
     * Return the search field by passing in the model attribute
     * @param type $attribute model attribute
     * @return type
     */
    public function getSearchField($attribute)
    {
        if (!empty($this->searchMap)){
            $flipped = array_flip($this->searchMap);
            logTrace(__METHOD__,$flipped);
            return $flipped[$attribute];
        }
        else
            return null;
    }
}