<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.TrendChart');
/**
 * Description of OrdersPurchasedPlusSpendingChart
 *
 * @author kwlok
 */
class OrdersPurchasedPlusSpendingChart extends TrendChart 
{
    const ID = 'OrdersPurchasedPlusSpendingChart';
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
        $currencySymbol = self::getCurrencySymbol(user()->getLocale(),$currency);
        return array(
            'id'=>self::ID,
            'type'=>self::TYPE,            
            'name'=>Sii::t('sii','Spending Trend').' '.$currencySymbol,
            'schema'=>array(
                'tableName'=>FactPurchase::model()->tableName(),
                'columns'=>array(
                    //array('title'=>Sii::t('sii','Items'),'column'=>'sum_items','xColumn'=>'date','bar'=>true),//bar
                    array('title'=>Sii::t('sii','Orders'),'column'=>'sum_orders','xColumn'=>'date','bar'=>true),//bar
                    array('title'=>Sii::t('sii','Total Spent'),'column'=>'spending','xColumn'=>'date'),//line, put second
                ),
                'queryCommand'=>array(
                    'select'=>'d.date, sum(expenditure) spending, sum(item_unit) sum_items, sum(order_unit) sum_orders',
                    'from'=>FactPurchase::model()->tableName().' f',
                    'join'=>array(
                        array('table'=>DimDate::model()->tableName().' d','condition'=>'f.date_id=d.id'),
                        array('table'=>DimCurrency::model()->tableName().' c','condition'=>'f.currency_id=c.id AND c.currency=\''.$currency.'\''),
                    ),
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
            'currencySymbol'=>$currencySymbol,
            'showLegend'=>true,
            'xAxisLabel'=>Sii::t('sii','Purchase Date'),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'').(isset($currency)?'_'.$currency:'')),
        );        
    }    
}
