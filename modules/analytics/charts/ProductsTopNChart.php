<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ProductsTopNChart
 * 
 * Note: this chart is incomplete in the sense that need a "others" group on top of the Top N
 *
 * @author kwlok
 */
class ProductsTopNChart 
{
    const ID    = 'ProductsTopNChart';
    const TYPE  = Chart::PIE_CHART;
    const LIMIT = 5;
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
            'name'=>Sii::t('sii','Top Products'),
            'schema'=>array(
                'tableName'=>FactSale::model()->tableName(),
                'columns'=>array(
                    array('group'=>'name','column'=>'sum'),
                ),
                'queryCommand'=>array(
                    'from'=>'(SELECT product_id, name, sum(total_price) sum FROM `s_item` WHERE shop_id = '.$shop.' group by product_id) sum_table',
                    'order'=>'sum desc',
                    'limit'=>5,
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
            'showLegend'=>false,
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );        
    }
}
