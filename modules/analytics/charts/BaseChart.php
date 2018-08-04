<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BaseChart
 *
 * @author kwlok
 */
class BaseChart 
{
    /**
     * Get chart id
     * @param type $shop
     * @param type $currency
     * @return type
     */
    public static function getChartId($id,$shop,$currency)
    {
        return 'widget_'.$id.(isset($shop)?'_'.$shop:'').(isset($currency)?'_'.$currency:'');
    }
    /**
     * @return currency symbol
     */
    protected static function getCurrencySymbol($locale,$currency)
    {
        $symbol = CLocale::getInstance($locale)->getCurrencySymbol($currency);
        return isset($symbol)?$symbol:'$';
    }    
    /**
     * Tooltip
     * @param type $content
     * @return type
     */
    public static function getTooltip($content)
    {
        return Yii::app()->controller->widget('common.widgets.stooltip.SToolTip',array('content'=>$content,'config'=>array('position'=>SToolTip::POSITION_BOTTOM,'cssClass'=>SToolTip::WIDTH_200)),true);
    }
    
}
