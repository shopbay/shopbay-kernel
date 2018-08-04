<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.TrendChart');
/**
 * Description of OrdersQuantityPlusSalesChart
 *
 * @author kwlok
 */
class OrdersQuantityPlusSalesChart extends TrendChart 
{
    const ID = 'OrdersQuantityPlusSalesChart';
    const TYPE = Chart::LINE_PLUS_BAR_CHART;
    /**
     * Configuration to instantiate Chart widget
     * @param type $filterOption
     * @param type $shop
     * @param type $currency
     * @return array
     */
    public static function config($filterOption=null,$shop=null,$currency=null)
    {
        return array(
            'id'=>self::ID,
            'type'=>self::TYPE,            
            'name'=>Sii::t('sii','Sales'),
            'schema'=>array(
                'tableName'=>FactSale::model()->tableName(),
                'columns'=>array(
                    array('title'=>Sii::t('sii','Orders'),'column'=>'order_unit','xColumn'=>'date','bar'=>true),//bar
                    array('title'=>Sii::t('sii','Sales'),'column'=>'revenue','xColumn'=>'date'),//line, put second
                ),
                'queryCommand'=>array(
                    'select'=>'date, revenue, order_unit',
                    'from'=>FactSale::model()->tableName().' f',
                    'join'=>array('table'=>DimDate::model()->tableName().' d','condition'=>'f.date_id=d.id'),
                    'where'=>self::constructWhereClause($filterOption, $shop),
                ),                 
            ),
            'filter'=>array(
                Chart::FILTER_ACCOUNT=>user()->getId(),
                Chart::FILTER_SHOP=>$shop,
                Chart::FILTER_CURRENCY=>$currency,
                Chart::FILTER_OPTIONS=>array(
                    'type'=>Chart::FILTER_OPTION_DAY_OFFSET,
                    'value'=>$filterOption,
                ),           
            ),
            'currencySymbol'=>self::getCurrencySymbol(user()->getLocale(),$currency),
            'showLegend'=>true,
            'xAxisLabel'=>Sii::t('sii','Purchase Date'),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'').(isset($currency)?'_'.$currency:'')),
        );
        
    }    
}
