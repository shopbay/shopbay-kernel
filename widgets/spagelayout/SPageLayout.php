<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SPageLayout
 *
 * @author kwlok
 */
class SPageLayout extends SWidget
{
    const COLUMN_LEFT = 'sidebar left';
    const COLUMN_RIGHT = 'sidebar right';
    const COLUMN_CENTER = 'content center';
    const COLUMN_MAIN = 'content main';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.spagelayout.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'spagelayout';
    /**
     * The width of page layout. Default to 'auto' => style="width:auto;"
     * @var string
     */
    public $width;      
    /**
     * The columns defintion of page layout. 
     * @var array
     */
    public $columns = [];      
    /**
     * The columns html options defintion of page layout. Array key should matched to $columns 
     * example 
     * <pre>
     * $columns = array('column-left'=>'<div>column content</div>');
     * $columnsHtmlOptions = array('column-left'=>array('style'=>'width:150px'));
     * </pre>
     * @var array
     */
    public $columnsHtmlOptions = [];      
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (count($this->columns)==0)
            throw new CException(Sii::t('sii','SPageLayout must have column definitions'));
        
        $this->render('index');
    }
    
    protected function getColumnHtmlOptions($column)
    {
        $htmlOptions = array('class'=>'page-'.$column);
        if (isset($this->columnsHtmlOptions[$column])){
            $htmlOptions = array_merge($htmlOptions, $this->columnsHtmlOptions[$column]);
        }
        if (isset($htmlOptions['cssClass'])){
            $htmlOptions['class'] .= ' '.$htmlOptions['cssClass'];
            unset($htmlOptions['cssClass']);
        }
            
        return $htmlOptions;
    }
    
    const WIDTH_05PERCENT = 'width-p-5';
    const WIDTH_10PERCENT = 'width-p-10';
    const WIDTH_15PERCENT = 'width-p-15';
    const WIDTH_20PERCENT = 'width-p-20';
    const WIDTH_25PERCENT = 'width-p-25';
    const WIDTH_30PERCENT = 'width-p-30';
    const WIDTH_33PERCENT = 'width-p-33';
    const WIDTH_35PERCENT = 'width-p-35';
    const WIDTH_38PERCENT = 'width-p-38';
    const WIDTH_45PERCENT = 'width-p-45';
    const WIDTH_50PERCENT = 'width-p-50';
    const WIDTH_62PERCENT = 'width-p-62';
    const WIDTH_65PERCENT = 'width-p-65';
    const WIDTH_75PERCENT = 'width-p-75';
    const WIDTH_80PERCENT = 'width-p-80';
    const WIDTH_85PERCENT = 'width-p-85';
    const WIDTH_90PERCENT = 'width-p-90';
    const WIDTH_95PERCENT = 'width-p-95';
    const WIDTH_99PERCENT = 'width-p-99';
    
    public static function parseCenterWithPercent($sideWidthPercent,$offset=0)
    {
        $width = explode('-', $sideWidthPercent);
        return 100 - $width[2] - $offset;
    }
    
    public static function getCenterWidthPercent($sideWidthPercent,$side2WidthPercent=null)
    {
        $centerWidth = function($default) use ($side2WidthPercent) {
            if ($side2WidthPercent!=null){
                $offset = self::parseCenterWithPercent($default);
                return 'width-p-'.self::parseCenterWithPercent($side2WidthPercent,$offset);
            }
            else
                return $default;
        };
        switch ($sideWidthPercent) {
            case SPageLayout::WIDTH_05PERCENT:
                return $centerWidth(SPageLayout::WIDTH_95PERCENT);
            case SPageLayout::WIDTH_10PERCENT:
                return $centerWidth(SPageLayout::WIDTH_90PERCENT);
            case SPageLayout::WIDTH_15PERCENT:
                return $centerWidth(SPageLayout::WIDTH_85PERCENT);
            case SPageLayout::WIDTH_20PERCENT:
                return $centerWidth(SPageLayout::WIDTH_80PERCENT);
            case SPageLayout::WIDTH_25PERCENT:
                return $centerWidth(SPageLayout::WIDTH_75PERCENT);
            case SPageLayout::WIDTH_35PERCENT:
                return $centerWidth(SPageLayout::WIDTH_65PERCENT);
            case SPageLayout::WIDTH_38PERCENT:
                return $centerWidth(SPageLayout::WIDTH_62PERCENT);
            case SPageLayout::WIDTH_50PERCENT:
                return $centerWidth(SPageLayout::WIDTH_50PERCENT);
            default:
                return $centerWidth(SPageLayout::WIDTH_99PERCENT);
        }
    }
    
    public static function getCenterHalfWidthPercent($divide=1)
    {
        switch ($divide) {
            case 1:
                return SPageLayout::WIDTH_62PERCENT;
            case 2:
                return SPageLayout::WIDTH_30PERCENT;
            case 3:
                return SPageLayout::WIDTH_20PERCENT;
            default:
                return SPageLayout::WIDTH_99PERCENT;
        }

    }
    public static function getWidthDividePercent($divide=1)
    {
        switch ($divide) {
            case 1:
                return SPageLayout::WIDTH_99PERCENT;
            case 2:
                return SPageLayout::WIDTH_50PERCENT;
            case 3:
                return SPageLayout::WIDTH_33PERCENT;
            case 4:
                return SPageLayout::WIDTH_25PERCENT;
            default:
                return SPageLayout::WIDTH_99PERCENT;
        }

    }
    
}
