<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.BaseChart');
/**
 * Description of SpendingGrowthChart
 *
 * @author kwlok
 */
class SpendingGrowthChart extends BaseChart
{
    const ID   = 'SpendingGrowthChart';
    const TYPE = Chart::GROWTH_CHART;
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
            'hasCurrencyColumn'=>true,
            'locale'=>user()->getLocale(),
            //'name'=>Sii::t('sii','Overview'),
            'schema'=>array(
                'tableName'=>FactPurchase::model()->tableName(),
                'columns'=>array(
                    array('title'=>Sii::t('sii','Total Spent'),'column'=>'expenditure','format'=>Chart::FORMAT_CURRENCY),
                ),
            ),            
            'filter'=>array(
                Chart::FILTER_ACCOUNT=>user()->getId(),
                Chart::FILTER_SHOP=>$shop,
                Chart::FILTER_CURRENCY=>$currency,
                Chart::FILTER_OPTIONS=>array(
                    'type'=>Chart::FILTER_OPTION_DATE_PERIOD,
                    'value'=>$filterOption,
                ),
            ),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>self::getChartId(self::ID,$shop,$currency)),
        );
    }
}
