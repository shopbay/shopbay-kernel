<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.TrendChart');
/**
 * Description of SpendingTrendChart
 *
 * @author kwlok
 */
class SpendingTrendChart extends TrendChart 
{
    const ID = 'SpendingTrendChart';
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
            'name'=>Sii::t('sii','Spending Trend'),
            'schema'=>array(
                'tableName'=>FactPurchase::model()->tableName(),
                'columns'=>array(
                    array('title'=>Sii::t('sii','Total Spent'),'column'=>'spending','color'=>'#ff7f0e','area'=>true),
                    //array('title'=>Sii::t('sii','Orders'),'column'=>'sum_orders','color'=>'red'),
                    //array('title'=>Sii::t('sii','Items'),'column'=>'sum_items','color'=>'blue'),
                ),
                'queryCommand'=>array(
                    'select'=>'date, sum(expenditure) spending, sum(item_unit) sum_items, sum(order_unit) sum_orders',
                    'from'=>FactPurchase::model()->tableName().' f',
                    'join'=>array('table'=>DimDate::model()->tableName().' d','condition'=>'f.date_id=d.id'),
                    'where'=>self::constructWhereClause($filterOption, $shop),
                    'group'=>array('date_id'),
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
            //'xAxisLabel'=>Sii::t('sii','Purchase Date'),
            'yAxisLabel'=>Sii::t('sii','Amount ($)'),    
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );        
    }
}
