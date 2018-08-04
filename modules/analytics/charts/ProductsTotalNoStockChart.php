<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.TotalChart');
/**
 * Description of ProductsTotalNoStockChart
 *
 * @author kwlok
 */
class ProductsTotalNoStockChart extends TotalChart 
{
    const ID = 'ProductsTotalNoStockChart';
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
            //'name'=>Sii::t('sii','Total Out of Stock Products'),
            'schema'=>array(
                'tableName'=>Product::model()->tableName(),
                'columns'=>array(
                    array(
                        //'title'=>Sii::t('sii','Products'),
                        'subscript'=>Sii::t('sii','Out of Stock'),
                        'column'=>'product_count',
                    ),
                ),
                'queryCommand'=>array(
                    'select'=>array('count(1) product_count'),
                    'where'=>'account_id='.user()->getId().' AND shop_id='.$shop.' AND id NOT IN (SELECT obj_id FROM s_inventory group by obj_type, obj_id having obj_type = \'s_product\' and sum(available) > 0 and shop_id = '.$shop.')',
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
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>self::getChartId(self::ID,$shop,$currency)),
        );
    }    
}
