<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of DashboardControllerBehavior
 *
 * @author kwlok
 */
abstract class DashboardControllerBehavior extends CBehavior 
{
    /**
     * Define how welcome controller will be initialized
     */
    abstract public function initBehavior();
    /**
     * To define which charts were to be loaded
     * @return array
     */
    abstract public function loadCharts();
    /**
     * To define how scope filters were to be loaded
     * @see SPageIndexController
     * @return array
     */
    abstract public function loadScopeFilters();
    /**
     * To define the widget view based on scope filters since we set $customWidgetView to true in init()
     * @see WelcomeController::init()
     * @see SPageIndexController
     * @return array
     */
    abstract public function loadWidgetView($view,$scope,$searchModel=null);
    /**
     * To define the default scope when index page is loaded
     * @see WelcomeController::init()
     * @see SPageIndexController
     * @return array
     */
    abstract public function loadDefaultScope();
    /**
     * Render chart widget
     * @param type $data
     * @return type
     */
    protected function renderChartWidget($data)
    {
        return ChartFactory::renderChartWidget($data['id'],isset($data['filter'])?$data['filter']:null,isset($data['shop'])?$data['shop']:null,isset($data['currency'])?$data['currency']:null);
    }
    /**
     * Render index page
     * @param type $content
     * @return type
     */
    abstract public function renderPageIndex($content);
        
}
