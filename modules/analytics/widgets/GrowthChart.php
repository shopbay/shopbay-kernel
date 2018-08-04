<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.ActiveChart');
/**
 * Description of GrowthChart
 *
 * @author kwlok
 */
class GrowthChart extends ActiveChart
{    
    public  $hasCurrencyColumn = false;
    public  $locale;
    private $_currency;//current processing currency
    private $_data;//current processing data
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::GROWTH_CHART;
        $this->height = '100%';
        $this->_data = new CList();
    }   
    /**
     * Get data for charting
     * @param type $metrics
     * @param type $filter
     * @return array
     */
    public function getData($metrics,$filter=null)
    {
        $currentData = $this->_executeDBCommand($this->getMetricColumns());
        //logTrace(__METHOD__.' current db data',$currentData);
        if ($this->hasCurrencyColumn)
            $this->_parseCurrencyData($metrics, $currentData);
        else 
            $this->_parseNormalData($metrics, $currentData);
        
        //logTrace(__METHOD__.' data',$this->_data);
        return $this->_data->toArray();
    }
    /**
     * Check if has currency value
     * @return type
     */
    public function hasCurrencyValue()
    {
        return $this->_currency!=null && $this->locale!=null;
    }    
    /**
     * Overridden method
     * @param type $value
     * @param type $format
     */
    public function formatMetric($value,$format)
    {
        if ($this->hasCurrencyColumn && $this->hasCurrencyValue()) {
            if (isset($format['format']) && $format['format']==Chart::FORMAT_CURRENCY)
                return CLocale::getInstance($this->locale)->numberFormatter->formatCurrency($value,$this->_currency);
        }
        
        if ($format==Chart::FORMAT_PERCENTAGE)
            return $this->_formatPercentage($value);
        
        return parent::formatMetric($value, $format);
    }    
    /**
     * Get data source data
     * @param type $columns
     * @param type $filter
     * @return type
     */
    private function _executeDBCommand($columns,$lastPeriod=0,$currency=null) 
    {                
        $select = 'd.'.$this->getFilterOptionValue().', ';
        if ($this->hasCurrencyColumn)
            $select .= 'c.currency, ';        
        foreach ($columns as $column) {
            if ($column!='currency')//skip currency column if any
                $select .= 'IFNULL(sum('.$column.'),0) '.$column.',';
        }
        
        $command = Yii::app()->db->createCommand()
                        ->select(substr($select, 0, -1))//remove last ','
                        ->from($this->getFactTable().' f')
                        ->join(DimDate::model()->tableName().' d', 'f.date_id=d.id');
        
        if ($this->hasCurrencyColumn){
            $joinCondition = 'f.currency_id=c.id';
            if (isset($currency))
                $joinCondition .= ' AND c.currency=\''.$currency.'\'';
            $command = $command->join(DimCurrency::model()->tableName().' c', $joinCondition)
                               ->where($this->_whereClause($lastPeriod))
                               ->group(array('d.'.$this->getFilterOptionValue(),'c.currency'));
            logTrace(__METHOD__.' query command = '.$command->text);
            if (isset($currency))
                return Yii::app()->db->createCommand($command->text)->queryRow();
            else
                return Yii::app()->db->createCommand($command->text)->queryAll();
        }
        else {
            $command = $command->where($this->_whereClause($lastPeriod))
                               ->group(array('d.'.$this->getFilterOptionValue()));            
            logTrace(__METHOD__.' query command = '.$command->text);
            return Yii::app()->db->createCommand($command->text)->queryRow();
        }
    } 
    
    private function _parseCurrencyData($metrics,$currentData)
    {
        foreach ($currentData as $record) {//loop through resultset; Each row is expected to contain one currency
            $this->_currency = $record['currency'];
            $previousPeriodData = $this->_executeDBCommand($this->getMetricColumns(),1,$this->_currency);
            $this->_parseNormalData($metrics, $record, $previousPeriodData);
        } 
        //if currentData has no data, pickup previous period data is any
        if (count($currentData)==0){
            $previousPeriodData = $this->_executeDBCommand($this->getMetricColumns(),1);
            $previousRecord = array();
            foreach ($metrics as $metric) {
                foreach ($previousPeriodData as $previousData) {
                    $this->_currency = $previousData['currency'];
                    $previousRecord[$metric['column']] = $previousData[$metric['column']];
                    break;//take first record currency
                }
            }
            $this->_parseNormalData($metrics, $currentData, $previousRecord);
        }
    }
    
    private function _parseNormalData($metrics, $currentData, $previousPeriodData=null)
    {
        if (!isset($previousPeriodData))
           $previousPeriodData = $this->_executeDBCommand($this->getMetricColumns(),1);
        //logTrace(__METHOD__.' previous period db data',$previousPeriodData);
        
        foreach ($metrics as $metric) {
            $currentTotal = isset($currentData[$metric['column']])?$currentData[$metric['column']]:0;
            $lastTotal = isset($previousPeriodData[$metric['column']])?$previousPeriodData[$metric['column']]:0;
            $growth = $this->_computeGrowth($currentTotal,$lastTotal);
            $this->_data->add(array(
                'title'=>$metric['title'],
                'total'=>$this->formatMetric($currentTotal,$metric),
                'growth'=>$growth,
                'subscript'=>$this->_growthSubscript($metric,$growth,$lastTotal),
            ));              
        }
    }
    
    private function _whereClause($lastPeriod)
    {
        $where = '';
        if (isset($this->filter)){
            $where = 'f.account_id = \''.$this->getFilterAccount().'\'';
            if ($this->getFilterOptionValue()==Chart::FILTER_PERIOD_DAY){
                $where .= ' AND d.'.$this->getFilterOptionValue().' = ('.$this->getFilterOptionValue().'(CURDATE()) - '.$lastPeriod.')';
                $where .= ' AND d.'.Chart::FILTER_PERIOD_MONTH.' = ('.Chart::FILTER_PERIOD_MONTH.'(CURDATE()) )';
                $where .= ' AND d.'.Chart::FILTER_PERIOD_YEAR.' = ('.Chart::FILTER_PERIOD_YEAR.'(CURDATE()) )';
            }
            else if ($this->getFilterOptionValue()==Chart::FILTER_PERIOD_WEEK){
                //have to make first day of week be Monday to be same as PHP
                $where .= ' AND d.'.$this->getFilterOptionValue().' = ('.$this->getFilterOptionValue().'(CURDATE(),1) - '.$lastPeriod.')';
                $where .= ' AND d.'.Chart::FILTER_PERIOD_YEAR.' = ('.Chart::FILTER_PERIOD_YEAR.'(CURDATE()) )';
            }
            else if ($this->getFilterOptionValue()==Chart::FILTER_PERIOD_MONTH){
                $where .= ' AND d.'.$this->getFilterOptionValue().' = ('.$this->getFilterOptionValue().'(CURDATE()) - '.$lastPeriod.')';
                $where .= ' AND d.'.Chart::FILTER_PERIOD_YEAR.' = ('.Chart::FILTER_PERIOD_YEAR.'(CURDATE()) )';
            }
            else {//period year
                $where .= ' AND d.'.$this->getFilterOptionValue().' = ('.$this->getFilterOptionValue().'(CURDATE()) - '.$lastPeriod.')';
            }
            
            if ($this->getFilterShop()!=null)
                $where .= ' AND f.shop_id = '.$this->getFilterShop();
        }
        return $where;
    }
    
    private function _computeGrowth($currentBase,$lastBase)
    {
        return $lastBase==0?$currentBase:round(($currentBase - $lastBase)/$lastBase,2);
    }

    private function _growthSubscript($metric,$growth,$lastTotal)
    {
        if ($lastTotal==0){
            return Sii::t('sii','{lastPeriod} Total: {lastTotal}',array(
                    '{lastPeriod}'=>$this->_lastPeriodDisplay($this->getFilterOptionValue()),
                    '{lastTotal}'=>$this->formatMetric($lastTotal,$metric)));        
        }
        else {
            return Sii::t('sii','{growth} {growthPeriod}, {lastPeriod} Total: {lastTotal}',array(
                    '{lastPeriod}'=>$this->_lastPeriodDisplay($this->getFilterOptionValue()),
                    '{lastTotal}'=>$this->formatMetric($lastTotal,$metric),
                    '{growth}'=>$this->formatMetric($growth,Chart::FORMAT_PERCENTAGE),
                    '{growthPeriod}'=>$this->_growthPeriodDisplay($this->getFilterOptionValue())));        
        }
    }
    
    private function _lastPeriodDisplay($period)
    {
        switch ($period) {
            case Chart::FILTER_PERIOD_DAY:
                return Sii::t('sii','Yesterday');
            case Chart::FILTER_PERIOD_WEEK:
                return Sii::t('sii','Last Week');
            case Chart::FILTER_PERIOD_MONTH:
                return Sii::t('sii','Last Month');
            case Chart::FILTER_PERIOD_YEAR:
                return Sii::t('sii','Last Year');
            default:
                return ucfirst($period);
        }
    }
    
    private function _growthPeriodDisplay($period)
    {
        switch ($period) {
            case Chart::FILTER_PERIOD_DAY:
                return Sii::t('sii','DoD');
            case Chart::FILTER_PERIOD_WEEK:
                return Sii::t('sii','WoW');
            case Chart::FILTER_PERIOD_MONTH:
                return Sii::t('sii','MoM');
            case Chart::FILTER_PERIOD_YEAR:
                return Sii::t('sii','YoY');
            default:
                return ucfirst($period);
        }
    }
    /**
     * Format percentage
     * @param int $value
     * @return type
     */
    private function _formatPercentage($value)  
    {
        if ($value===null) $value = 0;
        return ($value*100).'%';
    }   
    
}
