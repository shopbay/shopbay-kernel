<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of Chart widget
 * Structure:
 * <Container> 
 *   <Name> 
 *   <Filter> 
 *   <Canvas> 
 *     <svg> (if any)
 * 
 * @author kwlok
 */
abstract class Chart extends SWidget 
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.modules.analytics.widgets.chart.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'chart';  
    /**
     * string the asset bundle of the widget
     */
    public $assetBundle = array(
                            'css'=>array('nv.d3.min.css'),
                            'js'=>array('d3.v3.js','nv.d3.min.js'),
                        );  
    /**
     * Chart type
     */
    public $type;
    /**
     * Chart Name 
     */
    public $name;
    /*
     * Chart filter
     * Data format: 
     * array(
     *     Chart::FILTER_ACCOUNT=>user()->getId(),
     *     Chart::FILTER_SHOP=>$shop,
     *     Chart::FILTER_OPTIONS=>array(
     *         'type'=>Chart::FILTER_OPTION_DATE_PERIOD,//example
     *         'value'=>$filterOption,
     *     ),
     *  ),
     */
    public $filter;    
    /**
     * Chart Canvas Id of Chart widget
     */
    public $canvasId;
    /*
     * Chart margin; 
     */
    public $margin = array('top'=>50,'right'=>50,'bottom'=>50,'left'=>20);
    /*
     * Chart width; Default to "100%"
     */
    public $width = '100%';
    /*
     * Chart height; Default to "auto"
     */
    public $height = 'auto';  
    /**
     * Html options settings for Chart widget
     */
    public $htmlOptions;
    /**
     * @return array config data
     */
    public function getConfig() 
    {
        return array(
            'filter'=>$this->filter,
            'margin'=>$this->margin,
            'width'=>$this->width,
            'height'=>$this->height,
        );
    }
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        //Run validation
        $this->validate();
        
        //Assign widget id if not set
        if(!isset($this->htmlOptions['id']))
            $this->htmlOptions['id'] = $this->getId();
        
        //Assign widget css class if not set, or append
        if(isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = 'chart-container '.$this->htmlOptions['class'];
        else
            $this->htmlOptions['class'] = 'chart-container';
        
        //widget template
        $this->render('analytics.widgets.chart.views.index');
        
    }
    /**
     * Default validation rules
     * 
     * @throws CException
     */
    public function validate()
    {        
        if(!in_array($this->type,$this->getTypes()))
            throw new CException(Sii::t('sii','Invalid chart "{type}"',array('{type}'=>$this->type)));
        
        if (isset($this->filter)){
            if (!is_array($this->filter)){
                throw new CException(Sii::t('sii','Invalid chart filter'));
            }

            //filter account is mandatory, rest filter type is optional: shop, currency, options etc
            if (!in_array(self::FILTER_ACCOUNT,array_keys($this->filter)))
                throw new CException(Sii::t('sii','Missing chart filter "{type}"',array('{type}'=>$type)));
        }        
    }
    /**
     * Function to publish and register assets on page 
     * @throws CException
     */
    public function publishAssets()
    {
        foreach ($this->assetBundle['css'] as $css) {
            $this->registerCssFile($this->pathAlias.DIRECTORY_SEPARATOR.'css',$css);
        }
        foreach ($this->assetBundle['js'] as $js) {
            $this->registerScriptFile($this->pathAlias.DIRECTORY_SEPARATOR.'js',$js);
        }
    }  
    /**
     * Assign canvas id 
     * @return type
     */
    public function getCanvasId()
    {
        if ($this->canvasId===null){
            if(isset($this->htmlOptions['id']))
                $this->canvasId = $this->htmlOptions['id'].'_canvas';
            else
                $this->canvasId = $this->getId().'_canvas';
        }
        return $this->canvasId;
    }
    /**
     * Return chart rendering script
     * 
     * @return type
     */
    public function getChartScript()
    {
        $js = 'new Chart(\''.$this->type.'\',\'#'.$this->getCanvasId().'\','.json_encode(self::parseChartConfig($this->type, $this->getConfig())).').render();';
        logTrace(__METHOD__,$js);
        return $js;
    }
    /**
     * js script: 
     * ..
     * var Chart = function(type,selection,config) {
     *    ...
     * }
     * ...
     * @return init data to instantiate JS Chart widget
     */
    public function getChartScriptInitData($selection=null,$filterBar=false)
    {
        $data = array(
            'id'=>$this->id,
            'name'=>$this->name,
            'type'=>$this->type,
            'selection'=>isset($selection)?$selection:'#'.$this->getCanvasId(),
            'config' => Chart::parseChartConfig($this->type,$this->getConfig()),
        );
        if ($filterBar)
            $data = array_merge($data,array('filterBar'=>$this->getFilterBar()));
        return $data;
    }
    /*
     * List of chart type supported
     */
    const CHART_CONTAINER     = 'ChartContainer';
    const LINE_CHART          = 'LineChart';
    const GROWTH_CHART        = 'GrowthChart';
    const PIE_CHART           = 'PieChart';
    const VERTICAL_BAR_CHART  = 'VerticalBarChart';
    const COUNTER_CHART       = 'CounterChart';
    const TABULAR_CHART       = 'TabularChart';
    const LINE_PLUS_BAR_CHART = 'LinePlusBarChart';
    const HISTORICAL_BAR_CHART= 'HistoricalBarChart';
    /**
     * Return chart type list
     * @return type
     */
    public function getTypes()
    {
        return array(
            self::CHART_CONTAINER,
            self::LINE_CHART,
            self::GROWTH_CHART,
            self::PIE_CHART,
            self::VERTICAL_BAR_CHART,
            self::COUNTER_CHART,
            self::TABULAR_CHART,
            self::LINE_PLUS_BAR_CHART,
            self::HISTORICAL_BAR_CHART,
            //rest of other types supported
        );
    }
    /*
     * List of chart filter types supported
     */
    const FILTER_ACCOUNT  = 'account';
    const FILTER_SHOP     = 'shop';
    const FILTER_CURRENCY = 'currency';
    const FILTER_OPTIONS  = 'options';    
    /**
     * Return chart filter list
     * @return type
     */
    public function getFilterTypes()
    {
        return array(
            self::FILTER_SHOP,
            self::FILTER_ACCOUNT,
            self::FILTER_CURRENCY,
            self::FILTER_OPTIONS,
            //rest of other types supported
        );
    }
    /**
     * @return filter shop
     */
    public function getFilterAccount()
    {
        return isset($this->filter[self::FILTER_ACCOUNT])?$this->filter[self::FILTER_ACCOUNT]:null;
    }        
    /**
     * @return filter shop
     */
    public function getFilterShop()
    {
        return isset($this->filter[self::FILTER_SHOP])?$this->filter[self::FILTER_SHOP]:null;
    }        
    /**
     * @return filter currency
     */
    public function getFilterCurrency()
    {
        return isset($this->filter[self::FILTER_CURRENCY])?$this->filter[self::FILTER_CURRENCY]:null;
    }        
    /*
     * List of chart filter options supported
     */
    const FILTER_OPTION_NULL        = null;
    const FILTER_OPTION_QUANTUM     = 'quantum';
    const FILTER_OPTION_DATE_PERIOD = 'period';
    const FILTER_OPTION_DAY_OFFSET  = 'offset';
    /*
     * List of chart filter options supported
     */
    const FILTER_OFFSET_DAY_90    = 90;
    const FILTER_OFFSET_DAY_30    = 30;
    const FILTER_OFFSET_DAY_14    = 14;
    const FILTER_OFFSET_DAY_7     = 7;
    const FILTER_PERIOD_DAY       = 'day';
    const FILTER_PERIOD_WEEK      = 'week';
    const FILTER_PERIOD_MONTH     = 'month';
    const FILTER_PERIOD_YEAR      = 'year';
    const FILTER_QUANTUM_QUANTITY = 'quantity';
    const FILTER_QUANTUM_AMOUNT   = 'amount';
    /**
     * Return chart filter options
     * @return type
     */
    public function getFilterOptions($option)
    {
        if ($option==self::FILTER_OPTION_DATE_PERIOD){
            return array(
                self::FILTER_PERIOD_DAY=>Sii::t('sii','Day'),
                self::FILTER_PERIOD_WEEK=>Sii::t('sii','Week'),
                self::FILTER_PERIOD_MONTH=>Sii::t('sii','Month'),
                self::FILTER_PERIOD_YEAR=>Sii::t('sii','Year'),
            );
        }
        else if ($option==self::FILTER_OPTION_DAY_OFFSET){
            return array(
                self::FILTER_OFFSET_DAY_7=>Sii::t('sii','Last {n} days',array(self::FILTER_OFFSET_DAY_7)),
                self::FILTER_OFFSET_DAY_14=>Sii::t('sii','Last {n} days',array(self::FILTER_OFFSET_DAY_14)),
                self::FILTER_OFFSET_DAY_30=>Sii::t('sii','Last {n} days',array(self::FILTER_OFFSET_DAY_30)),
                self::FILTER_OFFSET_DAY_90=>Sii::t('sii','Last {n} days',array(self::FILTER_OFFSET_DAY_90)),
            );
        }
        else if ($option==self::FILTER_OPTION_QUANTUM){
            return array(
                self::FILTER_QUANTUM_QUANTITY=>Sii::t('sii','Quantity'),
                self::FILTER_QUANTUM_AMOUNT=>Sii::t('sii','Amount'),
            );
        }
        else
            return null;
    }  
    /**
     * @return if chart has filter options enabled
     */
    public function hasFilterOptions()
    {
        return isset($this->filter[self::FILTER_OPTIONS]);
    }
    /**
     * @return filter option type
     */
    public function getFilterOptionType()
    {
        return isset($this->filter[self::FILTER_OPTIONS]['type'])?$this->filter[self::FILTER_OPTIONS]['type']:null;
    }    
    /**
     * @return filter option value
     */
    public function getFilterOptionValue()
    {
        return isset($this->filter[self::FILTER_OPTIONS]['value'])?$this->filter[self::FILTER_OPTIONS]['value']:null;
    }       
    /**
     * Get filter option items
     * @param type $filterOption
     * @param type $shop
     * @param type $currency
     * @return array
     * @throws CExeption
     */
    public function getFilterOptionItems($filterOption,$shop=null,$currency=null)
    {
        $items = [];
        if ($this->getFilterOptionType()!=null){
            foreach ($this->getFilterOptions($this->getFilterOptionType()) as $option => $label) {
                $items[] = array('label'=>$label, 'url'=>'javascript:void(0);',
                                 'active'=>$filterOption==$option,'itemOptions'=>array('class'=>$option),
                                 'linkOptions'=>array('onclick'=>'query("'.$this->id.'","'.$this->type.'","'.urlencode('#'.$this->getCanvasId()).'","'.$option.'",'.(isset($shop)?$shop:'null').','.(isset($currency)?'"'.$currency.'"':'null').');'));
            }
        }
        return $items;
    }
    /**
     * @return Filter bar (in HTML format)
     */
    public function getFilterBar()
    {
        return $this->widget('zii.widgets.CMenu', array(
            'encodeLabel'=>false,                            
            'items'=> $this->getFilterOptionItems($this->getFilterOptionValue(),$this->getFilterShop(),$this->getFilterCurrency()),
        ),true);        
    }
    /*
     * List of chart formatting supported
     */
    const FORMAT_LOCALE     = 'locale';
    const FORMAT_IMAGE      = 'image';
    const FORMAT_CURRENCY   = '$';
    const FORMAT_PERCENTAGE = '%';
    /**
     * Parse chart config params and construct chart config object
     * 
     * @param type $chartType
     * @param type $config
     * @return \stdClass
     */
    public static function parseChartConfig($chartType,$config)
    {
        $configObj = new stdClass();
        $configObj->svgId = 'svg_'.$chartType;//used in dashboard.css
        $configObj->svgCssClass = $chartType;
        foreach (array_keys($config) as $key) {
            $configObj->{$key} = $config[$key];
        }
        return $configObj;
    }       
}