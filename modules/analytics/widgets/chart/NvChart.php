<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.ActiveChart');
/**
 * A base chart using underlying nvd3 library
 *
 * @author kwlok
 */
abstract class NvChart extends ActiveChart 
{
    /*
     * Chart x axis label; Default to "null"
     */
    public $xAxisLabel;
    /*
     * Chart y axis label; Default to "null"
     */
    public $yAxisLabel;     
    /*
     * X axis tick format; This follows d3 format specifier
     * Example: date format 'date~%d-%b-%Y'
     * for date, have to have prefix: "date~"
     * return d3.time.format('%d-%b-%Y')(d);
     * 
     * For non date:
     * return d3.format('$,.2f');
     */
    public $xAxisFormat;
    const  FORMAT_DATE_PREFIX = 'date~';
    /*
     * yAxis tick format; This follows d3 format specifier
     * Example: numeric with two decimal places ',.2f'
     */
    public $yAxisFormat;
    /**
     * @return array config data
     */
    public function getConfig() 
    {
        return array_merge(parent::getConfig(),array(
            'xAxisLabel'=>$this->xAxisLabel,
            'yAxisLabel'=>$this->yAxisLabel,
            'xAxisFormat'=>$this->xAxisFormat,
            'yAxisFormat'=>$this->yAxisFormat,
        ));
    }
    
}
