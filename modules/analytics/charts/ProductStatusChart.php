<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.StatusChart');
/**
 * Description of ProductStatusChart
 *
 * @author kwlok
 */
class ProductStatusChart extends StatusChart 
{
    const ID   = 'ProductStatusChart';
    const TYPE = Chart::PIE_CHART;
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
            //'name'=>Sii::t('sii','Products Status Distribution'),
            'schema'=>array(
                'tableName'=>Product::model()->tableName(),
                'columns'=>array(
                    array('group'=>'name','column'=>'sum'),
                ),
                'queryCommand'=>array(
                    'select'=>'\''.self::getStatusText(Process::PRODUCT_ONLINE).'\' name, count(1) sum',
                    'where'=>'shop_id = '.$shop.' and status = \''.Process::PRODUCT_ONLINE.'\'',
                    'union'=>'select \''.self::getStatusText(Process::PRODUCT_OFFLINE).'\' name, count(1) sum from '.Product::model()->tableName().' where shop_id = '.$shop.' and status = \''.Process::PRODUCT_OFFLINE.'\'',
                ),                
            ),
            'filter'=>array(
                Chart::FILTER_ACCOUNT=>user()->getId(),
                Chart::FILTER_SHOP=>$shop,
                Chart::FILTER_CURRENCY=>$currency,
                Chart::FILTER_OPTIONS=>array(
                    'type'=>Chart::FILTER_OPTION_NULL,
                    'value'=>$filterOption,
                ),
            ),
            'height'=>'280px',
            'showLegend'=>false,
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>self::getChartId(self::ID,$shop,$currency)),
        );
    }
}
