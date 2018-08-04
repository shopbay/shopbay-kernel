<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.NvChart');
/**
 * Description of VerticalBarChart
 *
 * @author kwlok
 */
class VerticalBarChart extends NvChart 
{    
    /**
     * Init widget
     */ 
    public function init()
    {
        parent::init();
        $this->type = Chart::VERTICAL_BAR_CHART;
        $this->margin = array('top'=>50,'right'=>50,'bottom'=>50,'left'=>80);
        $this->height = '300px';
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
        return self::samples();
    }
    /**
     * @return sample data
     */
    public static function samples()
    {
        $data = new CList();
        $data->add(array('label'=>'Group 1','value'=>1));     
        $data->add(array('label'=>'Group 2','value'=>2));     
        $data->add(array('label'=>'Group 3','value'=>3));     
        $data->add(array('label'=>'Group 4','value'=>4));   
        return array(array('key'=>'Vertical Bar Chart Key','values'=>$data->toArray()));
    }
}

