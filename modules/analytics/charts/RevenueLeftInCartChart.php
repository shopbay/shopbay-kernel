<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.carts.models.Cart');
Yii::import('common.modules.analytics.charts.TotalChart');
/**
 * Description of RevenueLeftInCartChart
 * 
 * This total only sums up item level total_price 
 * Order level discount across items, or taxes are not included in this total
 * 
 * @author kwlok
 */
class RevenueLeftInCartChart extends TotalChart
{
    const ID = 'RevenueLeftInCartChart';
    /**
     * Configuration to instantiate Chart widget
     * @param type $filterOption
     * @param type $shop
     * @param type $currency
     * @return array
     */
    public static function config($filterOption=null,$shop=null,$currency=null)
    {
        $tooltip = Sii::t('sii','This total does not include store level discount and tax');
                
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'name'=>Sii::t('sii','Revenue Left in Cart'),
            'schema'=>array(
                'tableName'=>Cart::model()->tableName(),
                'columns'=>array(
                    array(
                        //'title'=>Sii::t('sii','Revenue'),
                        'subscript'=>Sii::t('sii','Total Revenue Left').' '.self::getTooltip($tooltip),
                        'column'=>'revenue',
                        'format'=>Chart::FORMAT_CURRENCY,
                    ),
                ),
                'queryCommand'=>array(
                    'select'=>array('sum(total_price) revenue'),
                    'where'=>'shop_id='.$shop.' AND status NOT IN (\''.Process::CHECKOUT_CONFIRM.'\',\''.Process::ERROR.'\')',
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
