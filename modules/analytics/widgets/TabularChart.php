<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.ActiveChart');
/**
 * Description of TabularChart
 *
 * @author kwlok
 */
class TabularChart extends ActiveChart 
{        
    public $emptyText;
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::TABULAR_CHART;
        if (!isset($this->emptyText))
            $this->emptyText = Sii::t('sii','No Data Available');
    }   
    /**
     * Overridden
     * @see ActiveChart::getConfig()
     */
    public function getConfig()
    {
        return array_merge(parent::getConfig(), array(
            'emptyText'=>$this->emptyText,
        ));
    }    
    /**
     * Get data for charting
     * For demo, return self::samples();
     * 
     * @param type $metrics
     * @return array
     */
    public function getData($metrics) 
    {
        $table = new CList();
        $row = new CMap();
        //header row
        foreach ($metrics as $metric) {
            if (isset($metric['label']) && is_array($metric['label']))
                $row->add($metric['column'],$metric['label'][$this->getFilterOptionValue()]);
            else
                $row->add($metric['column'],isset($metric['label'])?$metric['label']:'');
        }   
        $table->add($row->toArray());
        //data row
        $dsData = $this->executeQueryCommand();
        foreach ($dsData as $record) {
            foreach ($metrics as $metric) {
                $row->add($metric['column'],$this->formatMetric($record[$metric['column']],$metric));
            }   
            $table->add($row->toArray());
        }
        if (count($dsData)==0)
            $table->clear();//remove header row when there is no record
        return $table->toArray();
    } 
    /**
     * Overridden
     * @see ActiveChart::formatMetric()
     */
    public function formatMetric($value,$format)
    {
        if (isset($format['format']) && is_array($format['format'])){//parsing format
            $format = $format['format'][$this->getFilterOptionValue()];
        }
        return parent::formatMetric($value, $format);
    }    
    /**
     * @return sample data
     */
    public static function samples()
    {
        $table = array(
            array('column1'=>'Header 1','column2'=>'Header 2','column3'=>'Header 3','column4'=>'Header 4','column5'=>'Header 5'),//header row 1
            array('column1'=>'Value 1','column2'=>'Value 2','column3'=>'Value 3','column4'=>'Value 4','column5'=>'Value 5'),//data row 1
            array('column1'=>'Value A','column2'=>'Value B','column3'=>'Value C','column4'=>'Value D','column5'=>'Value E'),//data row 2
        );
        return $table;
    }

}