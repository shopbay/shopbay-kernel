<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.yii-behavior-sluggable-master.SluggableBehavior');
/**
 * Description of SlugBehavior
 * Implement (override) simpleSlug method 
 *
 * @author kwlok
 */
class SlugBehavior extends SluggableBehavior 
{
    /**
     * Update the slug every time the row is updated?
     *
     * @var bool $update
     */
    public $update = false;
    /**
     * Inflector can be turned off, so only whitespaces are
     * replaced by dashes
     *
     * @var mixed
     * @access public
     */
    public $useInflector = false;
    /**
     * Characters to replace in the string to slugify
     */
    public $replace = array();    
    /**
     * Delimiter used in slug
     */
    public $delimiter = '-';    
    /**
     * Maximum length of slug
     */
    public $maxLength = 100;    
    /**
     * Model non-table field (or method) to be used for sluggable
     * array(
     *   'method'=>'method_name',
     *   'param'=>'column',
     * ),
     */
    public $dynamicColumn;    
    /**
     * Scenario to skip auto slug if there is already slug value presented in the model
     */
    public $skipScenario;    
    /**
     * OVERRIDEN
     * 
     * Supports non table columns: $this->dynamicColumn
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    public function beforeSave($event)
    {
        logTrace(__METHOD__.' skipScenario='.$this->skipScenario.' vs currentScenario='.$this->getOwner()->getScenario());
        
        if (isset($this->skipScenario) && $this->getOwner()->getScenario()==$this->skipScenario){
            if ($this->slugSkipScenario())
                return true;
        }
        
        try {
            parent::beforeSave($event);
        } catch (CException $ex) {
            //$columns not defined, try model attributes (dynamic column)
            logTrace(__METHOD__.' '.$ex->getMessage());
        }

        //Start trying to fetch model methods
        if (isset($this->dynamicColumn)){
            $this->slugDynamicColumn();
        }

    }    
    /**
     * Create a simple slug by just replacing white spaces, but with length limit and delimiter config
     * A modified version of @link SluggableBehavior->simpleSlug
     *
     * @param string $str
     * @access protected
     * @return void
     */
    public function simpleSlug($str)
    {
        if( !empty($this->replace) ) {
            $str = str_replace((array)$this->replace, ' ', $str);
        }
        $slug = preg_replace('@[\s!:;_\?=\\\+\*/%&#]+@', $this->delimiter, $str);
        if (true === $this->toLower) {
            $slug = mb_strtolower($slug, Yii::app()->charset);
        }
        $slug = trim(substr($slug, 0, $this->maxLength), '-');
        return $slug;
    } 
    /**
     * Slug method when a skip scenario is set
     * @return boolean
     */
    protected function slugSkipScenario() 
    {
        if (!empty($this->getOwner()->{$this->slugColumn})){
            logTrace(__METHOD__.' use existing slug value "'.$this->getOwner()->{$this->slugColumn}.'" from '.get_class($this->getOwner()).'.id='.$this->getOwner()->id);
            return true;//do nothing, use existing slug value from model
        }
        else {
            $this->slugDynamicColumn($this->skipScenario);
            return true;
        }
    }
    /**
     * Slug non-table columns (method based)
     * @throws CException
     */
    protected function slugDynamicColumn($scenario='')
    {
        logTrace(__METHOD__.' Trying dynamic column instead...');
        if (!isset($this->dynamicColumn['method']) && !isset($this->dynamicColumn['param']))
             throw new CException('Dyanmic column has incorrect data format format.');

        $values = [];
        $method = $this->dynamicColumn['method'];
        $slugValue = $this->getOwner()->$method($scenario);
        if ($slugValue!=false){
            logTrace(__METHOD__.' slug initial value = '.$slugValue);
            $values[] = $slugValue;
            //make slug
            $slug = $checkslug = $this->simpleSlug(
                implode('-', $values)
            );
            //check slug
            $this->checkUniqueSlug($slug,$checkslug);
        }
    }
    /**
     * Method below is an part of code fragment from parent SluggableBehavior
     * @see SluggableBehavior
     */
    protected function checkUniqueSlug($slug,$checkslug)
    {
        // Check if slug has to be unique
        if (false === $this->unique
            ||
            (! $this->getOwner()->getIsNewRecord()
            && $slug === $this->getOwner()->{$this->slugColumn})
        ) {
            logTrace(__METHOD__.' Non unique slug or slug already set');
            $this->getOwner()->{$this->slugColumn} = $slug;
        } else {
            $counter = 0;
            while ($this->getOwner()->resetScope()
                ->findByAttributes(array($this->slugColumn => $checkslug))
            ) {
                logTrace(__METHOD__." $checkslug found, iterating");
                $checkslug = sprintf('%s-%d', $slug, ++$counter);
            }
            $this->getOwner()->{$this->slugColumn} = $counter > 0 ? $checkslug : $slug;
        }
    }
    /**
     * Find model by slug
     * @param type $slug
     * @return \Page
     */
    public function withSlug($slug)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['slug'=>$slug]);
        $this->getOwner()->getDbCriteria()->mergeWith($criteria);
        return $this->getOwner();
    }  
}