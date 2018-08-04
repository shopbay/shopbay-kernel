<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.ContainerChart');
/**
 * Description of ShopVisitContainerChart
 *
 * @author kwlok
 */
class ShopVisitsContainerChart extends ContainerChart 
{
    const ID = 'ShopVisitsContainerChart';
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
            array('id'=>PageviewChart::ID,'type'=>PageviewChart::ID,'filter'=>Chart::FILTER_OFFSET_DAY_7,'shop'=>$shop,'currency'=>$currency),
            array('id'=>VisitorsChart::ID,'type'=>VisitorsChart::ID,'filter'=>Chart::FILTER_OFFSET_DAY_7,'shop'=>$shop,'currency'=>$currency),
            array('id'=>ConversionsChart::ID,'type'=>ConversionsChart::ID,'filter'=>Chart::FILTER_OFFSET_DAY_7,'shop'=>$shop,'currency'=>$currency),
            array('id'=>RevenueLeftInCartChart::ID,'type'=>RevenueLeftInCartChart::ID,'filter'=>Chart::FILTER_OPTION_NULL,'shop'=>$shop,'currency'=>$currency),
        );            
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'columnWidth'=>array('33%','33%','33%','100%'),//left 1% each for margin, 100% will go to next row       
            //'name'=>Sii::t('sii','Visits'),
            'charts'=>self::constructMemberCharts($widgets),
            'htmlOptions'=>array('style'=>'width:99%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );                
    }    
}
