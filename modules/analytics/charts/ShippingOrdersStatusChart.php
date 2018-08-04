<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.StatusChart');
/**
 * Description of ShippingOrdersStatusChart
 *
 * @author kwlok
 */
class ShippingOrdersStatusChart extends StatusChart
{
    const ID   = 'ShippingOrdersStatusChart';
    const MODEL = 'ShippingOrder';
    const MAIN_STATUS = Process::ORDER_FULFILLED;
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
            'name'=>Sii::t('sii','Shipping Orders'),
            'margin' => array('top'=>100,'right'=>0,'bottom'=>0,'left'=>0),
            'schema'=>array(
                'tableName'=>self::tableName(self::MODEL),
                'columns'=>array(
                    array('group'=>'name','column'=>'sum'),
                ),
                'queryCommand'=>array(
                    'select'=>'\''.self::getStatusText(self::MAIN_STATUS).'\' name, count(1) sum',
                    'where'=>'shop_id = '.$shop.' and status = \''.self::MAIN_STATUS.'\'',
                    'union'=>self::unions($shop,array(self::MAIN_STATUS)),
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
            'height'=>'280px',
            'showLegend'=>false,
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>self::getChartId(self::ID,$shop,$currency)),
        );
    }
    
    public static function unions($shop,$excludes=array())
    {
        $unions = new CList();
        $internalExcludes = array(Process::CANCELLED,Process::DEFERRED_CANCELLED);
        foreach (self::getAllStatus(self::tableName(self::MODEL),array_merge($excludes,$internalExcludes)) as $status) {
            $unions->add('select \''.self::getStatusText($status).'\' name, count(1) sum from '.self::tableName(self::MODEL).' where shop_id = '.$shop.' and status = \''.$status.'\'');
        }
        //handle $internalExcludes separately
        $unions->add('select \''.self::getStatusText(Process::CANCELLED).'\' name, count(1) sum from '.self::tableName(self::MODEL).' where shop_id = '.$shop.' and status IN (\''.Process::CANCELLED.'\',\''.Process::DEFERRED_CANCELLED.'\')');
        return $unions->toArray();
    }
}
