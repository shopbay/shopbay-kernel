<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.ContainerChart');
/**
 * Description of CustomerGrowthContainerChart
 *
 * @author kwlok
 */
class CustomerGrowthContainerChart extends ContainerChart 
{
    const ID = 'CustomerGrowthContainerChart';
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
            array('id'=>CustomerOrdersGrowthChart::ID,'type'=>CustomerOrdersGrowthChart::TYPE,'filter'=>Chart::FILTER_PERIOD_DAY,'shop'=>$shop,'currency'=>$currency),
            array('id'=>SpendingGrowthChart::ID,'type'=>SpendingGrowthChart::TYPE,'filter'=>Chart::FILTER_PERIOD_DAY,'shop'=>$shop,'currency'=>$currency),
        );            
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'columnWidth'=>array('100%','100%'),//left 1% each for margin       
            //'name'=>Sii::t('sii','Growth'),
            'charts'=>self::constructMemberCharts($widgets),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );                
    }
}

