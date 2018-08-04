<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.NvChart');
/**
 * Description of LinePlusBarChart
 *
 * @author kwlok
 */
class LinePlusBarChart extends NvChart 
{    
    /*
     * Indicate the currency symbol
     */
    public $currencySymbol = '$';     
    /*
     * Indicate if to show legend; Default to "true"
     * Legend itself supports filtering
     */
    public $showLegend = true;     
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::LINE_PLUS_BAR_CHART;
        $this->margin = array('top'=>30,'right'=>80,'bottom'=>50,'left'=>30);
        $this->height = '300px';
    }
    /*
     *  Get config data
     */
    public function getConfig() 
    {
        return array_merge(parent::getConfig(),array(
            'currencySymbol'=>$this->currencySymbol,
            'showLegend'=>$this->showLegend,
        ));        
    }    
    /**
     * Get data for charting
     * For demo: return self::samples();
     * 
     * @param type $metrics
     * @return array
     */
    public function getData($metrics) 
    {
        $dsData = $this->executeQueryCommand();
        $barValues = [];
        $lineValues = [];
        //[1]construct bar and line values
        foreach ($dsData as $record) {
            foreach ($metrics as $metric) {
                if (isset($metric['bar']) && $metric['bar'])
                    $barValues[] = [$record[$metric['xColumn']],$this->formatMetric($record[$metric['column']],$metric)];
                else 
                    $lineValues[] = [$record[$metric['xColumn']],$this->formatMetric($record[$metric['column']],$metric)];
            }         
        }
        //logTrace(__METHOD__.' barValues',$barValues);
        //logTrace(__METHOD__.' lineValues',$lineValues);
        //[2]setup data array
        $data = [];
        foreach ($metrics as $metric) {
            if (isset($metric['bar']) && $metric['bar'])
                $data[] = ['key'=>$metric['title'],'bar'=>true,'values'=>$barValues];
            else 
                $data[] = ['key'=>$metric['title'],'values'=>$lineValues];
        }         
        //logTrace(__METHOD__,$data);
        return $data;         
    }
    /**
     * @return sample data
     */
    public static function samples()
    {        
        return array(
            array(
                'key'=>'Quantity',
                'bar'=>true,
                'values'=>[ [ 1136005200000 , 1271000.0] , [ 1138683600000 , 1271000.0] , [ 1141102800000 , 1271000.0] , 
                            [ 1143781200000 , 0] , [ 1146369600000 , 0] , [ 1149048000000 , 0] , 
                            [ 1151640000000 , 0] , [ 1154318400000 , 0] , [ 1156996800000 , 0] , 
                            [ 1159588800000 , 3899486.0] , [ 1162270800000 , 3899486.0] , [ 1164862800000 , 3899486.0] , 
                            [ 1167541200000 , 3564700.0] , [ 1170219600000 , 3564700.0] , [ 1172638800000 , 3564700.0] , 
                            [ 1175313600000 , 2648493.0] , [ 1177905600000 , 2648493.0] , [ 1180584000000 , 2648493.0]
                        ],
            ),
            array(
                'key'=>'Price',
                'values'=>[ [ 1136005200000 , 71.89] , [ 1138683600000 , 75.51] , [ 1141102800000 , 68.49] ,
                            [ 1143781200000 , 62.72] , [ 1146369600000 , 70.39] , [ 1149048000000 , 59.77] , 
                            [ 1151640000000 , 57.27] , [ 1154318400000 , 67.96] , [ 1156996800000 , 67.85] , 
                            [ 1159588800000 , 76.98] , [ 1162270800000 , 81.08] , [ 1164862800000 , 91.66] , 
                            [ 1167541200000 , 84.84] , [ 1170219600000 , 85.73] , [ 1172638800000 , 84.61] , 
                            [ 1175313600000 , 92.91] , [ 1177905600000 , 99.8] , [ 1180584000000 , 121.191] 
                    ],
            ),
        );    
    }
}
