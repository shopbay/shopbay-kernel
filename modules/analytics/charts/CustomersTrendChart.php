<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.TrendChart');
/**
 * Description of CustomersTrendChart
 *
 * @author kwlok
 */
class CustomersTrendChart extends TrendChart 
{
    const ID = 'CustomersTrendChart';
    const TYPE = Chart::HISTORICAL_BAR_CHART;
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
            'name'=>Sii::t('sii','Trends'),
            'margin' => array('top'=>50,'right'=>20,'bottom'=>50,'left'=>70),
            'schema'=>array(
                'tableName'=>FactCustomer::model()->tableName(),
                'columns'=>array(
                    array('title'=>Sii::t('sii','Customers'),'column'=>'customer','area'=>true),
                ),
                'queryCommand'=>array(
                    'select'=>'date, count(customer_id) customer',
                    'from'=>FactCustomer::model()->tableName().' f',
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
            //'xAxisLabel'=>Sii::t('sii','Visit Date'),    
            'yAxisLabel'=>Sii::t('sii','Total'), 
            'xAxisFormat'=>NvChart::FORMAT_DATE_PREFIX.'%d-%b-%Y',
            'yAxisFormat'=>',',  
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>self::getChartId(self::ID,$shop,$currency)),
        );        
    } 
}
