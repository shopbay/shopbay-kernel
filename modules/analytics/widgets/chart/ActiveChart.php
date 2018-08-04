<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.widgets.chart.Chart');
Yii::import('common.modules.analytics.widgets.chart.IActiveChart');
/**
 * Description of ActiveChart
 *
 * @author kwlok
 */
abstract class ActiveChart extends Chart implements IActiveChart
{
    private $_s;//shop instance
    /**
     * Chart schema (fact table and columns)
     * array(
     *   'tableName'=>'<table_name>',
     *   'columns'=>array(
     *      array('title'=>'<title>','color'=>'<color>','column'=>'<column_name>'),
     *      array('title'=>'<title>','color'=>'<color>','column'=>'<column_name>'),
     *      ...
     *   ),
     * )
     */
    public $schema;
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(),array(
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'localeOwner',
            ),              
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),              
        ));
    }      
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        //Additional validation
        if(!isset($this->schema))
            throw new CException(Sii::t('sii','Chart schema not defined'));
        
        if(!isset($this->schema['tableName']))
            throw new CException(Sii::t('sii','Chart schema table not defined'));
        
        if(!isset($this->schema['columns']))
            throw new CException(Sii::t('sii','Chart schema columns not defined'));
        
        parent::run();
    }
    /**
     * @return array config data
     */
    public function getConfig() 
    {
        return array_merge(parent::getConfig(),array(
            'data'=>$this->getData($this->getMetrics()),
        ));
    }    
    /**
     * @return fact table name
     */
    public function getFactTable()
    {
        return $this->schema['tableName'];
    }
    /**
     * @return array fact metric name
     */
    public function getMetrics()
    {
        return $this->schema['columns'];
    } 
    /**
     * Get metric name
     * @return array
     */
    public function getMetricColumns()
    {
        $columns = array();
        foreach ($this->schema['columns'] as $metric) {
            array_push($columns, $metric['column']);
        }
        return $columns;
    }    
    /**
     * @return array fact metric name
     */
    protected function getQueryCommand()
    {
        return $this->schema['queryCommand'];
    }       
    /**
     * Execute database command to get data
     * @param string $extraWhere condition
     * @return type
     */
    protected function executeQueryCommand($extraWhere=null,$queryCommand=null) 
    {
        if (!isset($queryCommand))
            $queryCommand = $this->getQueryCommand();
        
        if (!isset($queryCommand['select']))
            $queryCommand['select'] = '*';
        
        if (!isset($queryCommand['from']))
            $queryCommand['from'] = $this->getFactTable();
        
        $command = Yii::app()->db->createCommand()
                        ->select($queryCommand['select'])             
                        ->from($queryCommand['from']);
        
        if (isset($queryCommand['join'])) {
            if (array_key_exists('table',$queryCommand['join'])){
                $command = $command->join($queryCommand['join']['table'],$queryCommand['join']['condition']);
            }
            else {
                foreach ($queryCommand['join'] as $join) {
                    $command = $command->join($join['table'],$join['condition']);
                }
            }
        }
        
        if (isset($extraWhere))
            $queryCommand['where'] = $queryCommand['where'].' AND '.$extraWhere;
        
        if (isset($queryCommand['where']))
            $command = $command->where($queryCommand['where']);
        
        if (isset($queryCommand['group']))
            $command = $command->group($queryCommand['group']);
        
        if (isset($queryCommand['order']))
            $command = $command->order($queryCommand['order']);
        
        if (isset($queryCommand['limit']))
            $command = $command->limit($queryCommand['limit']);
        
        if (isset($queryCommand['union'])){
            if (is_array($queryCommand['union'])){
                foreach ($queryCommand['union'] as $union) {
                    $command = $command->union($union);
                }
            }
            else
                $command = $command->union($queryCommand['union']);
        }
            
        logTrace(__METHOD__.' query command = '.$command->text);
        //query db data
        $data = Yii::app()->db->createCommand($command->text)->queryAll();
        //logTrace(__METHOD__.' data',$data);
        return $data;
    }      
    public function getLocaleOwner()
    {
        if (isset($this->filter[Chart::FILTER_SHOP])){
            if (!isset($this->_s))
                $this->_s = Shop::model()->findByPk($this->filter[Chart::FILTER_SHOP]);
            return $this->_s;
        }
        else 
            return null;
    }    
    
    public function formatMetric($value,$format)
    {
        if ($this->localeOwner!=null && is_array($format)){//coming as metric array
            if (isset($format['format']) && $format['format']==Chart::FORMAT_CURRENCY)
                return $this->formatCurrency($value);
            if (isset($format['format']) && $format['format']==Chart::FORMAT_PERCENTAGE)
                return $this->formatPercentage($value);
            if (isset($format['format']) && $format['format']==Chart::FORMAT_LOCALE && isset($format['locale']))
                return Helper::rightTrim($this->parseLanguageValue($value,$format['locale']),28);
            if (isset($format['format']) && $format['format']==Chart::FORMAT_IMAGE)
                return CHtml::image($value, Sii::t('sii','Image'), array('width'=>30));
        }
        if ($this->localeOwner!=null && is_scalar($format)){
            if (isset($format) && $format==Chart::FORMAT_CURRENCY)
                return $this->formatCurrency($value);
            if (isset($format) && $format==Chart::FORMAT_PERCENTAGE)
                return $this->formatPercentage($value);
        }
        return $value;//return back value if not format required
    }
}
