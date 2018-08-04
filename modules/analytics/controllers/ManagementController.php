<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends ShopParentController 
{
    public function init()
    {
        parent::init();
        $this->modelType = 'Shop';//dummy used for ServiceNotAvailableAction
        //-----------------
        // ShopParentController Configuration
        //-----------------
        $this->showBreadcrumbsModule = true;
        $this->breadcrumbsModuleName = Sii::t('sii','Dashboard');        
        $this->showBreadcrumbsController = false;
        $this->loadSessionParentShop();
        //-----------------
        // SPageIndex Configuration
        // @see SPageIndexController
        $this->viewName = Sii::t('sii','Dashboard');
        $this->route = 'analytics/management/index';
        $this->pageControl = SPageIndex::CONTROL_ARROW;
        $this->enableViewOptions = false;
        $this->enableSearch = false;
        $this->customWidgetView = true;
        $this->defaultScope = $this->getDefaultScope();
        $this->controlCallback = $this->getControlCallback();
        //-----------------
        // Exclude following actions from rights filter 
        //-----------------
        $this->rightsFilterActionsExclude = [
            $this->serviceNotAvailableAction,
        ];
        //-----------------//
    }      
    /**
     * This controller will not include 'shopsubscription' filter when {@link SubscriptionFilter} is not enabled at config/main.php
     * Note that the name of {@link SubscriptionFilter} must be 'subcription'
     * As shop subscription is used for validation merchant subscription
     * For shop customer, no need to go through this filter 
     * 
     * We need to do this extra logic is due to this controller extends {@link ShopParentController} which is used to govern merchant shop objects.
     * 
     * @return array action filters
     */
    public function filters()
    {
        $filters = parent::filters();
        //if SubscriptionFilter is not found in filter rules (specified at main.php)
        if (!in_array('subscription',[Yii::app()->filter->rules])){
            foreach ($filters as $key => $value) {
                if ($value=='shopsubscription')
                    unset($filters[$key]);//remove parent filter
            }
        }
        return $filters;        
    }    
    /**
     * A callback when view page control is changed
     * @return type
     */
    public function getControlCallback()
    {
        return CHtml::encode('function(){refreshdashboard(\'/analytics/management/dashboard\');}');
    }
    /**
     * Behaviors for this controller
     */
    public function behaviors()
    {
        if ($this->hasDashboardBehavior){
            return array_merge(parent::behaviors(),[
                'dashboardbehavior' => [
                    'class'=>$this->module->dashboardControllerBehavior,
                ],
            ]);
        }
        else
            throw new CException('DashboardControllerBehavior is not set.');
    }         
    /**
     * @return boolean if welcome behavior is enabled
     */
    public function getHasDashboardBehavior()
    {
        return isset($this->module->dashboardControllerBehavior);
    }     
    /**
     * Below action is called when js:quickdashboard() is invoked - when user is switching 'Arrow Tabs' at index page
     */
    public function actionDashboard()
    {
        $widget = new CMap();
        $charts = $this->loadCharts();
        foreach ($charts as $chart){
            $widget->add($chart['id'],ChartFactory::getChartWidgetInitData([
                    'id'=>$chart['id'],
                    'type'=>$chart['type'],
                    'filter'=>$chart['filter'],
                    'shop'=>$this->hasParentShop()?$this->getParentShop()->id:null,
                    'currency'=>$this->hasParentShop()?$this->getParentShop()->currency:null,
                    'selection'=>null,
                ]));
        }
        header('Content-type: application/json');
        echo CJSON::encode($widget->toArray());
        Yii::app()->end();
    }    
    /**
     * Get default scope when index page is loaded
     * @return type
     */
    public function getDefaultScope()
    {
        return $this->loadDefaultScope();
    }    
    /**
     * OVERRIDE METHOD
     * @see SPageIndexController
     * @return array
     */
    public function getScopeFilters()
    {
        return $this->loadScopeFilters();
    }     
    /**
     * OVERRIDE METHOD
     * since we set $customWidgetView to true in init()
     * 
     * @see SPageIndexController
     * @return string view file name
     */
    public function getWidgetView($view,$scope=null,$searchModel=null)
    {
        return $this->loadWidgetView($view,$scope,$searchModel);
    } 
    /**
     * This action suppors chart filtering
     */
    public function actionChart($id,$type,$selection,$filter,$shop,$currency)
    {
        header('Content-type: application/json');
        echo CJSON::encode(ChartFactory::getChartWidgetInitData([
            'id'=>$id,
            'type'=>$type,
            'selection'=>$selection,
            'filter'=>$filter,
            'shop'=>$shop,
            'currency'=>$currency,
        ]));
        Yii::app()->end();              
    }    

    public function getDashboardMetrics()
    {
        return new CArrayDataProvider(Yii::app()->serviceManager->getAnalyticManager()->getAccountMetrics(user()->getId()),[
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
            'sort'=>false,
        ]);
    }      

}
