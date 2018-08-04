<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.NvChart');
/**
 * Description of LineChart
 *
 * @author kwlok
 */
class LineChart extends NvChart 
{    
    /*
     * Indicate if to show legend; Default to "false"
     */
    public $showLegend = false;
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::LINE_CHART;
        $this->margin = array('top'=>30,'right'=>50,'bottom'=>50,'left'=>80);
        $this->height = '300px';
    }       
    /**
     * Get config data
     * 
     * @return array
     */
    public function getConfig()
    {
        return array_merge(parent::getConfig(),array(
            'showLegend'=>$this->showLegend,
        ));           
    }
    /**
     * Get data for charting
     * @param type $metrics
     * @return array
     */
    public function getData($metrics) 
    {
        $dsData = $this->executeQueryCommand();
        //add line into lines 
        $lines = new CList();
        foreach ($metrics as $metric) {
            $values = new CList();
            foreach ($dsData as $value) {
                $values->add(array('x'=>$value['date'],'y'=>$value[$metric['column']]));
            }
            $lines->add(array(
                'area'=>isset($metric['area'])?$metric['area']:false,
                'key'=>$metric['title'],
                'color'=>isset($metric['color'])?$metric['color']:null,
                'values'=>$values->toArray(),
            ));              
        }
        //logTrace(__METHOD__,$lines->toArray());
        return $lines->toArray();      
    }   
    
}
