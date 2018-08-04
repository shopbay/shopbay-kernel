<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.spage.SPage");
/**
 * Description of SPageIndex
 *
 * @author kwlok
 */
class SPageIndex extends SPage
{
    const CONTROL_TAB = '_control_tab';
    const CONTROL_ARROW = '_control_arrow';
    const VIEW_LIST = '_listview';
    const VIEW_GRID = '_gridview';  
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.spageindex.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'spageindex';
    /**
     * Current page scope.
     * @var string 
     */
    public $scope;
    /**
     * The model class to use this page 
     * @var string 
     */
    public $model;
    /**
     * Page control. Default to 'arrow control'
     * Options:
     * [1] arrow control
     * [2] tab control 
     * @var string 
     */
    public $control;
    /**
     * Page control callback
     * format:
     * array(
     *  <scope1>=><callback function>,
     *  <scope2>=><callback function>,
     * )
     * 
     * @var string 
     */
    public $controlCallback;
    /**
     * Page view name. 
     * @var string 
     */
    public $viewName;
    /**
     * Enable view options. Default to true;
     * @var string 
     */
    public $enableViewOptions = true;
    /**
     * Page view file name. Default to 'list view'
     * @var string 
     */
    public $view = self::VIEW_LIST;
    /**
     * Container filters (array of control units)
     * @var object 
     */
    public $filters;
    /**
     * Widget to be embedded in the Page view
     * [1] CGridview
     * [2] CListview
     * @var CWidget 
     */
    public $widget;
    /**
     * The route to forward (in filter control) when a filter scope is clicked
     */
    public $route;
    /**
     * Render partial directly on view (without calling entry file "index")
     * and returns its output without actual rendering it (echo)
     */
    public $renderPartial = false;
    /**
     * Indicate if to hide heading; Default to 'false'
     * @var booleans 
     */
    public $hideHeading = false;
    /**
     * Indicate the locale this widget is running; Default to 'null'
     * This is required for css formatting of the arrow
     * @var booleans 
     */
    public $locale;
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->_validate();

        if ($this->renderPartial){
            return $this->render($this->view,array('config'=>$this->getConfig()),true);
        }
        else
            $this->render('index');
    }
    
    protected function getConfig()
    {
        return array('scope'=>$this->scope,
                     'model'=>$this->model,
                     'widget'=>$this->widget,
                );
    }	
    
    protected function getHeading()
    {
        if ($this->hideHeading)
            return null;
        else if (isset($this->heading))
            return $this->heading;
        else
            return $this->render('_heading',array(),true);
    }	
    
    protected function getDescription()
    {
        if (isset($this->description))
            return $this->description;
        else
            return null;
    }	    
    
    protected function getControlOnclick($filter) 
    {
        if (is_array($this->controlCallback)){
            $callback = isset($this->controlCallback[$filter])?$this->controlCallback[$filter]:'function(){}';            
        }
        else 
            $callback = isset($this->controlCallback)?$this->controlCallback:'function(){}';
        return 'filter(\''.$this->route.'\',\''.$this->model.'\',\''.$filter.'\','.$callback.')';
    }

    private function _validate() 
    {
        //validate mandatory fields
        $mandatoryFields = array('scope','model'); 
        foreach ($mandatoryFields as $field) {
            if ($this->{$field}==null)
                throw new CException(Sii::t('sii','{class} must have {field}',array('{class}'=>__CLASS__,'{field}'=>$field)));            
        }
        if (isset($this->control)){
            if ($this->filters==null)
                throw new CException(Sii::t('sii','{class} must have filters',array('{class}'=>__CLASS__)));            
        }
    }
    
    /**
     * Page view options name. 
     * Options:
     * [1] gridview (_gridview)
     * [2] listview (_listview)
     * @var string 
     */
    public static function getViewOptions()
    {
        return array(
            'GridView'=>SPageIndex::VIEW_GRID,
            'ListView'=>SPageIndex::VIEW_LIST,
        );
    }

}
