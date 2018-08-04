<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.BaseChart');
/**
 * Description of StatusChart
 *
 * @author kwlok
 */
class StatusChart extends BaseChart 
{
    const TYPE = Chart::PIE_CHART;
    
    public static function tableName($model)
    {
        return $model::model()->tableName();
    }
    public static function getStatusText($process)
    {
        return Process::getDisplayText(Process::getText($process));
    }
    public static function getAllStatus($objType,$excludes=array(),$startBy=null)
    {
        return array_keys(WorkflowManager::getAllProcesses($objType,$excludes,$startBy));
    }
    public static function getAllEndProcesses($objType,$excludes=array(),$startBy=null)
    {
        return array_keys(WorkflowManager::getAllEndProcesses($objType,$excludes,$startBy));
    }    
    public static function getAllStartProcesses($objType,$excludes=array(),$startBy=null)
    {
        return array_keys(WorkflowManager::getAllStartProcesses($objType,$excludes,$startBy));
    }      
}
