<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.NvChart');
/**
 * Description of PieChart
 *
 * @author kwlok
 */
class PieChart extends NvChart 
{    
    /*
     * Indicate if to show legend; Default to "true"
     * For PieChart, legend itself supports filtering
     */
    public $showLegend = true;       
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::PIE_CHART;
    }
    /*
     *  Get config data
     */
    public function getConfig() 
    {
        return array_merge(parent::getConfig(),array(
            'showLegend'=>$this->showLegend,
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
        $dsData = $this->executeQueryCommand();
        $values = new CList();
        foreach ($metrics as $metric) {
            foreach ($dsData as $record) {
                $values->add(array('key'=>$this->parseLanguageValue($record[$metric['group']],user()->getLocale()),'value'=>$this->formatMetric($record[$metric['column']],$metric)));
            }         
        }
        //logTrace(__METHOD__,$values);
        return $values->toArray();         
    }
    /**
     * @return sample data
     */
    public static function samples()
    {
        $data = new CList();
        $data->add(array('key'=>'Group 1','value'=>1));     
        $data->add(array('key'=>'Group 2','value'=>2));     
        $data->add(array('key'=>'Group 3','value'=>3));     
        $data->add(array('key'=>'Group 4','value'=>4));   
        return $data->toArray();
    }
}
