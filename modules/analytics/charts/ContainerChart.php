<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ContainerChart
 *
 * @author kwlok
 */
class ContainerChart 
{
    const TYPE = Chart::CHART_CONTAINER;
    /**
     * Construct container charts to be attached to ChartContainer
     * @param type $widgets Declared widgets to be attached
     * @return type
     */
    protected static function constructMemberCharts($widgets)
    {
        $charts = new CList();
        //get each counter chart config, and stores chart init data
        foreach ($widgets as $widget) {
            $config = ChartFactory::getChartWidgetConfig($widget['id'],$widget['filter'],$widget['shop'],$widget['currency']);
            $w = new $config['type']();
            $w->init();
            foreach ($config as $key => $value){
                $w->$key = $value;
            }
            $charts->add($w->getChartScriptInitData('#'.$w->getCanvasId(),true));
        }
        return $charts->toArray();
    }

}
