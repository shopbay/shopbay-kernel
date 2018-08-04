<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.BaseChart');
Yii::import('common.modules.customers.models.Customer');
/**
 * Description of CustomersTopNTable
 *
 * @author kwlok
 */
class CustomersTopNTable extends BaseChart 
{
    const ID    = 'CustomersTopNTable';
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
                $sumColumn = 'amount';
                break;
            case Chart::FILTER_QUANTUM_QUANTITY:
                $sumColumn = 'order_unit';
                break;
            default:
                $sumColumn = 'undefined';
                break;
        }                
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'name'=>Sii::t('sii','Top Customers'),
            'schema'=>array(
                'tableName'=>FactCustomer::model()->tableName(),
                'columns'=>array(
                    array('label'=>Sii::t('sii','Name'),'column'=>'alias_name'),
                    array('label'=>Sii::t('sii','Item Count'),'column'=>'sum_item_unit'),
                    array('label'=>array(
                                    Chart::FILTER_QUANTUM_AMOUNT=>Sii::t('sii','Total Amount'),
                                    Chart::FILTER_QUANTUM_QUANTITY=>Sii::t('sii','Total Orders'),
                                ),
                          'column'=>'sum',
                          'format'=>array(
                                    Chart::FILTER_QUANTUM_AMOUNT=>Chart::FORMAT_CURRENCY,
                                    Chart::FILTER_QUANTUM_QUANTITY=>null,
                                ),
                        ),
                ),
                'queryCommand'=>array(
                    'select'=>'f.customer_id, c.alias_name, SUM(item_unit) sum_item_unit, SUM('.$sumColumn.') sum',
                    'from'=>FactCustomer::model()->tableName().' f',
                    'join'=>array('table'=>Customer::model()->tableName().' c','condition'=>'f.customer_id = c.customer_id AND f.account_id = c.account_id'),
                    'where'=>'f.account_id = \''.user()->getId().'\' AND f.shop_id = '.$shop,
                    'group'=>array('f.customer_id'),
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
