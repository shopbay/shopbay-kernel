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
class CustomerOrdersContainerChart extends ContainerChart 
{
    const ID = 'CustomerOrdersContainerChart';
    /**
     * Configuration to instantiate Chart widget
     * @param type $filterOption
     * @param type $shop
     * @param type $currency
     * @return array
     */
    public static function config($filterOption=null,$shop=null,$currency=null)
    {
        $colWidth = new CList();
        $widgets = [];            
        foreach (self::_getCurrencies() as $record) {
            $widgets[] = array('id'=>OrdersPurchasedPlusSpendingChart::ID,'type'=>OrdersPurchasedPlusSpendingChart::TYPE,'filter'=>Chart::FILTER_OFFSET_DAY_7,'shop'=>$shop,'currency'=>$record['currency']);
            $colWidth->add('48%');
        }
        $widgets[] = array('id'=>CustomerItemsStatusChart::ID,'type'=>CustomerItemsStatusChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency);
        $colWidth->add('25%');
        $widgets[] = array('id'=>CustomerPOsStatusChart::ID,'type'=>CustomerPOsStatusChart::TYPE,'filter'=>$filterOption,'shop'=>$shop,'currency'=>$currency);
        $colWidth->add('25%');
        return array(
            'id'=>self::ID,            
            'type'=>self::TYPE,            
            'columnWidth'=>$colWidth->toArray(),//left 1% each for margin       
            //'name'=>Sii::t('sii','Orders'),
            'charts'=>self::constructMemberCharts($widgets),
            'htmlOptions'=>array('style'=>'width:96.5%','id'=>'widget_'.self::ID.(isset($shop)?'_'.$shop:'')),
        );                
    }
    /**
     * @return available currencies (purchased before)
     */
    private static function _getCurrencies()
    {
        $command = Yii::app()->db->createCommand()
                        ->select('c.currency')             
                        ->from(FactPurchase::model()->tableName().' f')
                        ->join(DimCurrency::model()->tableName().' c','f.currency_id=c.id')
                        ->where('account_id = \''.user()->getId().'\'')
                        ->group('currency_id');
            
        logTrace(__METHOD__.' query command = '.$command->text);
        //query db data
        $data = Yii::app()->db->createCommand($command->text)->queryAll();
        //logTrace(__METHOD__.' data',$data);
        return $data;
    }
    
}
