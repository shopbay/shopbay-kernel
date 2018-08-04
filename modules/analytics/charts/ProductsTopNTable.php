<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.BaseChart');
/**
 * Description of ProductsTopNTable
 *
 * @author kwlok
 */
class ProductsTopNTable extends BaseChart
{
    const ID    = 'ProductsTopNTable';
    const TYPE  = Chart::TABULAR_CHART;
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
        switch ($filterOption) {
            case Chart::FILTER_QUANTUM_AMOUNT:
                $sumColumn = 'total_price';
                break;
            case Chart::FILTER_QUANTUM_QUANTITY:
                $sumColumn = 'quantity';
                break;
            default:
                $sumColumn = 'undefined';
                break;
        }                
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'name'=>Sii::t('sii','Best Selling Products'),
            'schema'=>array(
                'tableName'=>FactSale::model()->tableName(),
                'columns'=>array(
                    array('label'=>null,'column'=>'image','format'=>Chart::FORMAT_IMAGE),
                    array('label'=>Sii::t('sii','Name'),'column'=>'name','format'=>Chart::FORMAT_LOCALE,'locale'=>user()->getLocale()),
                    array('label'=>array(
                                    Chart::FILTER_QUANTUM_AMOUNT=>Sii::t('sii','Total Sales'),
                                    Chart::FILTER_QUANTUM_QUANTITY=>Sii::t('sii','Total Qty'),
                                ),
                          'column'=>'sum',
                          'format'=>array(
                                    Chart::FILTER_QUANTUM_AMOUNT=>Chart::FORMAT_CURRENCY,
                                    Chart::FILTER_QUANTUM_QUANTITY=>null,
                                ),
                        ),
                ),
                'queryCommand'=>array(
                    'from'=>'(SELECT product_id, product_image image, name, sum('.$sumColumn.') sum FROM `s_item` WHERE shop_id = '.$shop.' group by product_id) sum_table',
                    'order'=>'sum desc',
                    'limit'=>self::LIMIT,
                ),                  
            ),
            'filter'=>array(
                Chart::FILTER_ACCOUNT=>user()->getId(),
                Chart::FILTER_SHOP=>$shop,
                Chart::FILTER_CURRENCY=>$currency,
                Chart::FILTER_OPTIONS=>array(
                    'type'=>Chart::FILTER_OPTION_QUANTUM,
                    'value'=>$filterOption,
                ),
            ),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>self::getChartId(self::ID,$shop,$currency)),
        );
        
    }
}
