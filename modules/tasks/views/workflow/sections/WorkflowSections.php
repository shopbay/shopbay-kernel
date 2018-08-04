<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of WorkflowSections
 *
 * @author kwlok
 */
abstract class WorkflowSections extends CComponent
{
    //section view; relative to controller path
    public $transitionHistoryView = '_transition_history';//within module
    public $itemsView = '../item/_items';//within module
    public $orderSummaryView = '../order/_summary';//within module
    public $refundSummaryView = '../order/_summary_refund';//within module    
    
    private $_s;//sections placeholder
    private $_c;//controller
    private $_m;//model
    /**
     * Constructor.
     * @param mixed $controller the workflow controller
     * @param mixed $model the workflow model
     */
    public function __construct($controller,$model)
    {
            $this->_c = $controller;
            $this->_m = $model;
            $this->_s = new CList();
    }    
    
    protected function getController()
    {
        return $this->_c;
    }
    
    protected function getModel()
    {
        return $this->_m;
    }

    protected function getSections()
    {
        return $this->_s;
    }
    /**
     * Add custom section
     * @param type $section
     * @return type
     */
    public function add($section=[])
    {
        return $this->_s->add($section);
    }
    
    public function getData($default=true)
    {
        if ($default)
            $this->prepareData();
        return $this->_s->toArray();
    }
    
    abstract public function prepareData();
        
}
