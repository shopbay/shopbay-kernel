<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.TotalChart');
/**
 * Description of ConversionsChart
 *
 * @author kwlok
 */
class ConversionsChart extends TotalChart
{
    const ID = 'ConversionsChart';
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
            'name'=>Sii::t('sii','Conversions'),
            'schema'=>array(
                'tableName'=>FactVisit::model()->tableName(),
                'columns'=>array(
                    array(
                        'title'=>Sii::t('sii','Added to Cart'),
                        'column'=>'v_addcart',
                        'subscript'=>array('column'=>'addcart_ratio','format'=>Chart::FORMAT_PERCENTAGE),
                    ),
                    array(
                        'title'=>Sii::t('sii','Checkout'),
                        'column'=>'v_checkout',
                        'subscript'=>array('column'=>'checkout_ratio','format'=>Chart::FORMAT_PERCENTAGE),
                    ),
                    array(
                        'title'=>Sii::t('sii','Purchased'),
                        'column'=>'v_purchased',
                        'subscript'=>array('column'=>'purchased_ratio','format'=>Chart::FORMAT_PERCENTAGE),
                    ),
                ),
                'queryCommand'=>array(
                    'select'=>'(v_addcart/visitors) addcart_ratio, (v_checkout/visitors) checkout_ratio, (v_purchased/visitors) purchased_ratio, visitors, v_addcart, v_checkout, v_purchased',
                    'from'=>'('.self::baseQuery($filterOption, $shop).') as base',
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
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>self::getChartId(self::ID,$shop,$currency)),
        );        
    }
    
    public static function baseQuery($filterOption,$shop)
    {
        $select = 'count(distinct visitor) visitors, SUM(LEAST(1,addcart)) v_addcart, SUM(LEAST(1,checkout)) v_checkout, SUM(LEAST(1,purchased)) v_purchased';
        return Yii::app()->db->createCommand()
                        ->select($select)
                        ->from(FactVisit::model()->tableName().' f')
                        ->join(DimDate::model()->tableName().' d','f.date_id=d.id')
                        ->where(self::whereClause($filterOption,$shop))
                        ->text;
    }
    
    public static function whereClause($filterOption,$shop)
    {
        $where = 'f.account_id = \''.user()->getId().'\'';
        $where .= ' AND d.date > ( CURDATE() - INTERVAL '.$filterOption.' DAY )';
        if ($shop!=null)
            $where .= ' AND f.shop_id = '.$shop;
        
        return $where;
    }
}
