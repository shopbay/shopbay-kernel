<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of AnalyticManager
 *
 * @author kwlok
 */
class AnalyticManager extends ServiceManager 
{
    //AnalyticManager operation constants
    const INCREASE = 1;
    const DECREASE = -1;
    const QUANTUM  = 1;
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }   
    /**
     * Return metric value
     * 
     * @param type $obj_type
     * @param type $obj_id
     * @param type $fact Metric fact name
     * @return int
     */
    public function getMetricValue($obj_type, $obj_id, $fact)
    {
        $model = Metric::model()->genericMetric($obj_type,$obj_id,$fact)->find();
        if ($model===null)
            return 0;//default value
        else
            return $model->value;        
    }
    /**
     * Return metric object
     * 
     * @param type $obj_type
     * @param type $obj_id
     * @param type $fact Metric fact name
     * @return CActiveRecord
     */
    public function getMetric($obj_type, $obj_id, $fact)
    {
        $model = Metric::model()->genericMetric($obj_type,$obj_id,$fact)->find();
        if ($model===null)
            throw new CException(Sii::t('sii','Dashboard Metric "{fact}" not found',array('{fact}'=>$fact)));
        return $model;
    }
    /**
     * Return metrics 
     * 
     * @param type $obj_type
     * @param type $obj_id
     * @return CActiveRecord
     */
    public function getMetrics($obj_type, $obj_id)
    {
        return Metric::model()->genericMetric($obj_type,$obj_id)->findAll();
    }
    /**
     * Return account metrics 
     * 
     * @param type $account_id
     * @return CActiveRecord
     */
    public function getAccountMetrics($account_id)
    {
        return Metric::model()->accountMetric($account_id)->findAll();
    }
    /**
     * Set Account metric
     * 
     * @param type $account_id
     * @param type $fact
     * @param type $quantum
     * @param type $mode
     * @throws CException
     */
    public function setAccountMetric($account_id,$fact,$quantum=self::QUANTUM,$mode=self::INCREASE)
    {
        $this->updateCounterMetric(Account::model()->tableName(), $account_id, $fact, $mode, $quantum, $mode);
    }
    /**
     * Set Shop metric
     * 
     * @param type $shop_id
     * @param type $fact
     * @param type $quantum
     * @param type $mode
     * @throws CException
     */
    public function updateShopMetric($shop_id,$fact,$quantum=self::QUANTUM,$mode=self::INCREASE)
    {
        $this->updateCounterMetric(Shop::model()->tableName(), $shop_id, $fact, $mode, $quantum, $mode);
    }   
    /**
     * Set Shop metric
     * 
     * @param type $obj_type
     * @param type $obj_id
     * @param type $fact
     * @param type $quantum
     * @param type $mode
     * @throws CException
     */
    public function updateCounterMetric($obj_type, $obj_id, $fact,$quantum=self::QUANTUM,$mode=self::INCREASE)
    {
        $model = Metric::model()->genericMetric($obj_type,$obj_id,$fact)->find();
        if ($model===null){
            $model = new Metric($obj_type,$obj_id,$fact);
            $model->value = 0;
        }
        $model->value += $mode==self::DECREASE?-$quantum:$quantum;
        $model->save();
        if ($model->hasErrors()){
            logError(__METHOD__.' Fail to update counter metric '.$fact,$model->getErrors());
            throw new CException(Sii::t('sii','Fail to update counter metric'));
        }
        logInfo(__METHOD__.' ok '.$fact.' '.($mode==self::INCREASE?'+'.$quantum:'-'.$quantum));
    }      
    /**
     * Set counter metric 
     * 
     * @param type $obj_type
     * @param type $obj_id
     * @param type $fact
     * @param type $quantum
     * @return type
     */
    public function setCounterMetric($obj_type, $obj_id, $fact, $quantum=self::QUANTUM)
    {
        $model = Metric::model()->genericMetric($obj_type,$obj_id,$fact)->find();
        if ($model===null){
            $model = new Metric($obj_type,$obj_id,$fact);
            $model->value = $quantum;
        }
        else
            $model->value = $model->value + $quantum;
        $model->save();
        logInfo(__METHOD__.' ok '.$fact,$model->getAttributes());
        return $model->value;
    }
    /**
     * Update order sale
     * 
     * @see AnalyticManager::_updateFactSale()
     */
    public function updateOrderSale($account_id, $shop_id, $unit, $revenue, $currency)
    {
        $dims = array('currency_id'=>$this->_parseDim('DimCurrency', $currency));
        $metrics = array('order_unit'=>$unit,'revenue'=>$revenue);
        $this->_updateFactSale($account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok for new order_unit='.$unit.', revenue='.$revenue);
    }
    /**
     * Update item sale
     * 
     * @see AnalyticManager::_updateFactSale()
     */
    public function updateItemSale($account_id, $shop_id, $unit, $currency)
    {
        $dims = array('currency_id'=>$this->_parseDim('DimCurrency', $currency));
        $metrics = array('item_unit'=>$unit);
        $this->_updateFactSale($account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok for new item_unit='.$unit);
    }
    /**
     * Update order purchase
     * 
     * @see AnalyticManager::_updateFactPurchase()
     */
    public function updateOrderPurchase($account_id, $shop_id, $unit, $expenditure, $currency)
    {
        $dims = array('currency_id'=>$this->_parseDim('DimCurrency', $currency));
        $metrics = array('order_unit'=>$unit,'expenditure'=>$expenditure);
        $this->_updateFactPurchase($account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok for new order_unit='.$unit.', expenditure='.$expenditure);
    }
    /**
     * Update item purchase
     * 
     * @see AnalyticManager::_updateFactPurchase()
     */
    public function updateItemPurchase($account_id, $shop_id, $unit, $currency)
    {
        $dims = array('currency_id'=>$this->_parseDim('DimCurrency', $currency));
        $metrics = array('item_unit'=>$unit);
        $this->_updateFactPurchase($account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok for new item_unit='.$unit);
    }
    /**
     * Update fact purchase (Wrapper method of AnalyticManager::_updateFactTable())
     * 
     * @see AnalyticManager::_updateFactTable()
     */
    private function _updateFactPurchase($account_id, $shop_id, $dims, $metrics=array())
    {
        $this->_updateFactTableByDims('FactPurchase', $account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok',$metrics);
    }    
    /**
     * Update fact sale (Wrapper method of AnalyticManager::_updateFactTable())
     * 
     * @see AnalyticManager::_updateFactTable()
     */
    private function _updateFactSale($account_id, $shop_id, $dims, $metrics=array())
    {
        $this->_updateFactTableByDims('FactSale', $account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok',$metrics);
    }  
    /**
     * Track customer purchase
     */
    public function trackCustomer($account_id, $shop_id, $customer_id, $order_unit, $item_unit, $amount, $currency)
    {
        $dims = array('customer_id'=>$customer_id,'currency_id'=>$this->_parseDim('DimCurrency', $currency));
        $metrics = array('order_unit'=>$order_unit,'item_unit'=>$item_unit,'amount'=>$amount);
        $this->_updateFactTableByDims('FactCustomer', $account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok',array_merge($dims,$metrics));        
    }
    /**
     * Track shop visit
     * Pageview: each page hit of a shop will be recorded
     * Visitor: unique visitor count; For login user, this will be account_id; For guest, this will be IP address
     */
    public function trackShopVisit($account_id, $shop_id, $visitor, $pageview=1)
    {
        $dims = array('visitor'=>$visitor);
        $metrics = array('pageview'=>$pageview);
        $this->_updateFactTableByDims('FactVisit', $account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok',array_merge($dims,$metrics));        
    }
    /**
     * Track shop add cart page visit
     */
    public function trackAddCartVisit($account_id, $shop_id, $visitor, $unit=1)
    {
        $metrics = array('addcart'=>$unit);
        $this->trackCartVisit($account_id, $shop_id, $visitor, $metrics);
    }      
    /**
     * Track shop checkout page visit
     */
    public function trackCheckoutVisit($account_id, $shop_id, $visitor, $unit=1)
    {
        $metrics = array('checkout'=>$unit);
        $this->trackCartVisit($account_id, $shop_id, $visitor, $metrics);
    }      
    /**
     * Track shop purchased page visit (order placed) 
     */
    public function trackPurchasedVisit($account_id, $shop_id, $visitor, $unit=1)
    {
        $metrics = array('purchased'=>$unit);
        $this->trackCartVisit($account_id, $shop_id, $visitor, $metrics);
    }      
    /**
     * Track shop add cart visit
     * Pageview: each page hit of a shop will be recorded
     * Visitor: unique visitor count; For login user, this will be account_id; For guest, this will be IP address
     */
    public function trackCartVisit($account_id, $shop_id, $visitor, $metrics)
    {
        $dims = array('visitor'=>$visitor);
        $this->_updateFactTableByDims('FactVisit', $account_id, $shop_id, $dims, $metrics);
        logInfo(__METHOD__.' ok',array_merge($dims,$metrics));        
    }    
    /**
     * Generic update fact table for Analytics
     * 
     * @param type $factTable
     * @param type $account_id
     * @param type $shop_id
     * @param type $dims Array of metrics to be updated
     * @param type $metrics Array of metrics to be updated
     * @return type
     */
    private function _updateFactTableByDims($factTable,$account_id, $shop_id, $dims, $metrics=array())
    {
        $dims = array_merge($dims,array(
            'account_id'=>$account_id,
            'shop_id'=>$shop_id,
            'date_id'=>$this->_parseDim('DimDate', new DateTime()),//today datetime
        ));
        
        if (!empty($metrics)){
            $model = $factTable::model()->retrieveByDims($dims)->find();
            if ($model===null){
                $model = new $factTable();
                foreach ($dims as $key => $value)
                    $model->$key = $value;
                foreach ($metrics as $key => $value)
                    $model->$key = $value;
                $model->save();
                logTrace(__METHOD__.' '.$factTable.' created', $model->getAttributes());
            }
            else {
                foreach ($metrics as $key => $value)
                    $model->$key += $value;
                $model->save();
                logTrace(__METHOD__.' existing '.$factTable.' updated', $model->getAttributes());
            }
        }        
    }
    
    private function _parseDim($dim,$value)
    {
        $model = $dim::model()->retrieve($value);
        if ($model===null)
            throw new CException('Dimention not found');
        return $model->id;
    }
}
