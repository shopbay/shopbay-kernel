<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.NvChart');
/**
 * Description of HistoricalBarChart
 *
 * @author kwlok
 */
class HistoricalBarChart extends NvChart 
{    
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::HISTORICAL_BAR_CHART;
        $this->margin = array('top'=>50,'right'=>50,'bottom'=>50,'left'=>80);
        $this->height = '300px';
    }
    /**
     * Get data for charting
     * @param type $metrics
     * @return array
     */
    public function getData($metrics) 
    {
        $dsData = $this->executeQueryCommand();
        //add bar into bars 
        $bars = new CList();
        foreach ($metrics as $metric) {
            $values = new CList();
            foreach ($dsData as $value) {
                $values->add(array('x'=>$value['date'],'y'=>$value[$metric['column']]));
            }
            $bars->add(array(
                'key'=>$metric['title'],
                'color'=>isset($metric['color'])?$metric['color']:null,
                'values'=>$values->toArray(),
            ));              
        }
        //logTrace(__METHOD__,$lines->toArray());
        return $bars->toArray();      
    }   
}
