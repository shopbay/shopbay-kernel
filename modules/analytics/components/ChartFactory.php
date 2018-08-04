<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.Chart');
Yii::import('common.modules.analytics.widgets.chart.NvChart');
Yii::import('common.modules.analytics.widgets.*');
/**
 * Description of ChartFactory
 *
 * @author kwlok
 */
class ChartFactory extends CComponent
{
    /**
     * List of charts to be included for customers
     * @return type
     */
    public static function getCustomerCharts($scope=null)
    {
        if (!isset($scope)){
            return array(
                array('id'=>CustomerGrowthContainerChart::ID,'type'=>CustomerGrowthContainerChart::TYPE,'filter'=>null,'shop'=>null,'currency'=>null),
                array('id'=>CustomerOrdersContainerChart::ID,'type'=>CustomerOrdersContainerChart::TYPE,'filter'=>null,'shop'=>null,'currency'=>null),
                //array('id'=>SpendingTrendChart::ID,'type'=>SpendingTrendChart::TYPE,'filter'=>Chart::FILTER_OFFSET_DAY_7),
            );
        }
        else {
            $charts = self::getCustomerCharts();
            $scopeCharts = [];
            foreach ($charts as $chart) {
                if ($chart['id']==$scope) {
                    $scopeCharts[] = $chart;
                    break;
                }
            }
            //logTrace(__METHOD__,$scopeCharts);
            return $scopeCharts;
        }        
    }
    /**
     * List of charts to be included for merchants
     * @return type
     */
    public static function getMerchantCharts($shop=null,$currency=null,$scope=null)
    {
        if (!isset($scope)){
            return array(
                array('id'=>ShopVisitsContainerChart::ID,'type'=>ShopVisitsContainerChart::TYPE,'filter'=>null,'shop'=>$shop,'currency'=>$currency),
                array('id'=>OrdersContainerChart::ID,'type'=>OrdersContainerChart::TYPE,'filter'=>null,'shop'=>$shop,'currency'=>$currency),
                array('id'=>ProductsContainerChart::ID,'type'=>ProductsContainerChart::TYPE,'filter'=>null,'shop'=>$shop,'currency'=>$currency),
                array('id'=>RevenueGrowthChart::ID,'type'=>RevenueGrowthChart::TYPE,'filter'=>Chart::FILTER_PERIOD_DAY,'shop'=>$shop,'currency'=>$currency),
                array('id'=>CustomersContainerChart::ID,'type'=>CustomersContainerChart::TYPE,'filter'=>null,'shop'=>$shop,'currency'=>$currency),
            );
        }
        else {
            $charts = self::getMerchantCharts($shop,$currency);
            $scopeCharts = [];
            foreach ($charts as $chart) {
                if ($chart['id']==$scope) {
                    $scopeCharts[] = $chart;
                    break;
                }
            }
            //logTrace(__METHOD__,$scopeCharts);
            return $scopeCharts;
        }
    }
    /**
     * List of charts to be included for merchants
     * @return type
     */
    public static function getMerchantQuickCharts($shop,$currency)
    {
        return array(
            array('id'=>ShopVisitsContainerChart::ID,'type'=>ShopVisitsContainerChart::TYPE,'filter'=>null,'shop'=>$shop,'currency'=>$currency),
            array('id'=>RevenueGrowthChart::ID,'type'=>RevenueGrowthChart::TYPE,'filter'=>Chart::FILTER_PERIOD_DAY,'shop'=>$shop,'currency'=>$currency),
        );
    }
    /**
     * Return chart name
     * @param type $id
     * @return type
     */
    public static function getChartName($id)
    {
        switch ($id) {
            case CustomerGrowthContainerChart::ID:
                return Sii::t('sii','Overview');
            case CustomerOrdersContainerChart::ID:
                return Sii::t('sii','Orders');
            case ShopVisitsContainerChart::ID:
                return Sii::t('sii','Visits');
            case OrdersContainerChart::ID:
                return Sii::t('sii','Orders');
            case ProductsContainerChart::ID:
                return Sii::t('sii','Products');
            case RevenueGrowthChart::ID:
                return Sii::t('sii','Growth');
            case CustomersContainerChart::ID:
                return Sii::t('sii','Customers');
            default:
                return Sii::t('sii','undefined');
        }
    }        
    /**
     * Render chart widget by controller
     * @param type $id
     * @param type $filterOption
     * @param type $shop
     * @return html 
     */
    public static function renderChartWidget($id,$filterOption,$shop=null,$currency=null)
    {
        $config = self::getChartWidgetConfig($id,$filterOption, $shop, $currency);
        //logTrace(__METHOD__,$config);
        return Yii::app()->controller->widget($config['type'], $config, true);
    }
    /**
     * Get chart widget config data
     * @param type $id
     * @param type $filterOption
     * @param type $shop
     * @return type
     * @throws CException
     */
    public static function getChartWidgetConfig($id,$filterOption,$shop=null,$currency=null)
    {
        try {            
            return $id::config($filterOption, $shop, $currency);            
        } catch (Exception $ex) {
            logError(__METHOD__.' '.$ex->getTraceAsString(),array(),false);
            throw new CException($ex->getMessage());
        }
    }
    /**
     * Create a chart widget instance based on input config data
     * @param type $config
     * @return \type
     * @throws CException
     */
    public static function createChartWidget($config)
    {
        $type = $config['type'];
        try {
            $id = $config['id'];
            $widget = new $type();
            $widget->init();
            foreach ($id::config($config['filter'],$config['shop']=='null'?null:$config['shop'],$config['currency']=='null'?null:$config['currency']) as $key => $value){
                $widget->$key = $value;
            }
            return $widget;
            
        } catch (Exception $ex) {
            logError(__METHOD__.' '.$ex->getMessage(),array(),false);
            throw new CException(Sii::t('sii','Unsupported chart {type}',array('{type}'=>$type)));
        }
    }    
    /**
     * @see Chart::getChartScriptInitData()
     * @param type $config
     * @return type
     */
    public static function getChartWidgetInitData($config)
    {
        $widget = ChartFactory::createChartWidget($config);
        return $widget->getChartScriptInitData(isset($config['selection'])?urldecode($config['selection']):'#'.$widget->getCanvasId());
    }   
}
