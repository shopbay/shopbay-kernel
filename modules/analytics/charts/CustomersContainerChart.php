<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.ContainerChart');
/**
 * Description of CustomersContainerChart
 *
 * @author kwlok
 */
class CustomersContainerChart extends ContainerChart 
{
    const ID = 'CustomersContainerChart';
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
            array('id'=>CustomersTotalChart::ID,'type'=>CustomersTotalChart::TYPE,'filter'=>null,'shop'=>$shop,'currency'=>$currency),
            array('id'=>CustomersTrendChart::ID,'type'=>CustomersTrendChart::TYPE,'filter'=>Chart::FILTER_OFFSET_DAY_7,'shop'=>$shop,'currency'=>$currency),
            array('id'=>CustomersTopNTable::ID,'type'=>CustomersTopNTable::TYPE,'filter'=>Chart::FILTER_QUANTUM_AMOUNT,'shop'=>$shop,'currency'=>$currency),
        );            
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'columnWidth'=>array('18%','45%','35%'),//left 1% each for margin       
            //'name'=>Sii::t('sii','Orders'),
            'charts'=>self::constructMemberCharts($widgets),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );                
    }
}
