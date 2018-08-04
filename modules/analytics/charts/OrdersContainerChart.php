<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.ContainerChart');
/**
 * Description of OrdersContainerChart
 *
 * @author kwlok
 */
class OrdersContainerChart extends ContainerChart 
{
    const ID = 'OrdersContainerChart';
    /**
     * Configuration to instantiate Chart widget
     * @param type $filterOption
     * @param type $shop
     * @param type $currency
     * @return array
     */
    public static function config($filterOption=null,$shop=null,$currency=null)
    {
        $widgets = array(
            array('id'=>OrdersTotalChart::ID,'type'=>OrdersTotalChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency),
            array('id'=>ShippingOrdersStatusChart::ID,'type'=>ShippingOrdersStatusChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency),
            array('id'=>OrdersQuantityPlusSalesChart::ID,'type'=>OrdersQuantityPlusSalesChart::TYPE,'filter'=>Chart::FILTER_OFFSET_DAY_7,'shop'=>$shop,'currency'=>$currency),
//            array('id'=>OrdersSalesTrendChart::ID,'type'=>OrdersSalesTrendChart::TYPE,'filter'=>Chart::FILTER_OFFSET_DAY_7,'shop'=>$shop,'currency'=>$currency),
//            array('id'=>OrdersSalesTotalChart::ID,'type'=>OrdersSalesTotalChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency),
        );            
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'columnWidth'=>array('30%','23%','45%'),//left 1% each for margin       
            //'name'=>Sii::t('sii','Orders'),
            'charts'=>self::constructMemberCharts($widgets),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );                
    }
}
