<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.TotalChart');
/**
 * Description of CustomersTotalChart
 *
 * @author kwlok
 */
class CustomersTotalChart extends TotalChart
{
    const ID = 'CustomersTotalChart';
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
            'name'=>Sii::t('sii','Totals'),
            'schema'=>array(
                'tableName'=>FactCustomer::model()->tableName(),
                'columns'=>array(
                    array(
                        //'title'=>Sii::t('sii','Customers'),
                        'subscript'=>Sii::t('sii','Total Customers'),
                        'column'=>'customers',
                    ),
                ),
                'queryCommand'=>array(
                    'select'=>array('count(customer_id) customers'),
                    'where'=>'account_id=\''.user()->getId().'\' AND shop_id='.$shop,
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
