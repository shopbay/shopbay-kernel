<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.Chart');
/**
 * Description of ChartContainer
 * Contains a group of chart widgets
 *
 * @author kwlok
 */
class ChartContainer extends Chart 
{    
    public $columnWidth;//column width
    public $charts = array();
    /**
     * @return array config data
     */
    public function getConfig() 
    {
        return array_merge(parent::getConfig(),array(
            'data'=>$this->getData(),
            'columnWidth'=>$this->columnWidth,
        ));
    }  
    /**
     * Get data for charting
     * 
     * @return array Charts
     */    
    public function getData() 
    {
        return $this->charts; 
    }

}