<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.ContainerChart');
/**
 * Description of ProductsContainerChart
 *
 * @author kwlok
 */
class ProductsContainerChart extends ContainerChart 
{
    const ID   = 'ProductsContainerChart';
    /**
     * Configuration to instantiate Chart widget
     * @param type $filterOption
     * @param type $shop
     * @param type $currency
     * @return array
     */
    public static function config($filterOption=null,$shop=null,$currency=null)
    {
        //$widgets to be included in container
        $widgets = array(
            array('id'=>ProductsTotalAllChart::ID,'type'=>ProductsTotalAllChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency),
            array('id'=>ProductsTotalNoStockChart::ID,'type'=>ProductsTotalNoStockChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency),
            array('id'=>ProductStatusChart::ID,'type'=>ProductStatusChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency),
            array('id'=>ProductsTopNTable::ID,'type'=>ProductsTopNTable::TYPE,'filter'=>Chart::FILTER_QUANTUM_AMOUNT,'shop'=>$shop,'currency'=>$currency),
        );            
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'columnWidth'=>array('18%','18%','20%','40%'),//left 1% each for margin              
            //'name'=>Sii::t('sii','Products'),
            'charts'=>self::constructMemberCharts($widgets),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );        
    }
}
