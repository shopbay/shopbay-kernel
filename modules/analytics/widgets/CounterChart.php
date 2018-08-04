<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.ActiveChart');
/**
 * Description of CounterChart
 *
 * @author kwlok
 */
class CounterChart extends ActiveChart 
{    
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::COUNTER_CHART;
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
        //logTraceDump(__METHOD__.' dsData',$dsData);
        $values = [];
        foreach ($metrics as $metric) {
            foreach ($dsData as $record) {
                $counter = $this->formatMetric($record[$metric['column']],$metric);
                if (isset($metric['subscript']) && is_array($metric['subscript']))
                    $subscript = $this->formatMetric($record[$metric['subscript']['column']],$metric['subscript']['format']);
                else
                    $subscript = isset($metric['subscript'])?$metric['subscript']:null;
                $values[] = array('title'=>isset($metric['title'])?$metric['title']:null,'counter'=>isset($counter)?$counter:0,'subscript'=>$subscript);
            }         
        }
        //logTrace(__METHOD__,$values);
        return $values; 
    }
    /**
     * @return sample data
     */
    public static function samples()
    {
        $data = array(
            'counter'=>rand(1, 100),
            'title'=>'Counter Title',
            'subscript'=>'Counter Subscript',
        );     
        return $data;
    }

}