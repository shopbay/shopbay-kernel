<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.StatusChart');
/**
 * Description of CustomerPOsStatusChart
 *
 * @author kwlok
 */
class CustomerPOsStatusChart extends StatusChart
{
    const ID   = 'CustomerPOsStatusChart';
    const MODEL = 'Order';
    const MAIN_STATUS = Process::PAID;
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
            'name'=>Sii::t('sii','Orders'),
            'margin' => array('top'=>100,'right'=>0,'bottom'=>0,'left'=>0),
            'schema'=>array(
                'tableName'=>self::tableName(self::MODEL),
                'columns'=>array(
                    array('group'=>'name','column'=>'sum'),
                ),
                'queryCommand'=>array(
                    'select'=>'\''.self::getStatusText(self::MAIN_STATUS).'\' name, count(1) sum',
                    'where'=>'account_id = \''.user()->getId().'\' and status = \''.self::MAIN_STATUS.'\'',
                    'union'=>self::unions(user()->getId(),array(self::MAIN_STATUS)),
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
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );
    }
    
    public static function unions($customer,$excludes=array())
    {
        $unions = new CList();
        foreach (self::getAllStatus(self::tableName(self::MODEL),$excludes,Role::CUSTOMER) as $status) {
            $unions->add('select \''.self::getStatusText($status).'\' name, count(1) sum from '.self::tableName(self::MODEL).' where account_id = \''.$customer.'\' and status = \''.$status.'\'');
        }
        //logTrace(__METHOD__,$unions);
        return $unions->toArray();
    }    
}
