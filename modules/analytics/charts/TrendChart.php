<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.charts.BaseChart');
/**
 * Description of TrendChart
 *
 * @author kwlok
 */
class TrendChart extends BaseChart
{
    const TYPE = Chart::LINE_CHART;
    /**
     * Construct query command WHERE clause
     * 
     * @param type $filterOption
     * @param type $shop
     * @return string
     */
    protected static function constructWhereClause($filterOption,$shop)
    {
        $where = 'f.account_id = \''.user()->getId().'\'';
        $where .= ' AND d.date > ( CURDATE() - INTERVAL '.$filterOption.' DAY )';
        if ($shop!=null)
            $where .= ' AND f.shop_id = '.$shop;
        return $where;
    }
    
}
