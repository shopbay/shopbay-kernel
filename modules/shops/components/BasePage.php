<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BasePage
 *
 * @author kwlok
 */
abstract class BasePage extends CComponent 
{   
    /**
     * Basic page
     */
    const HOME    = 'home_page';//landing page
    /**
     * Page mode
     */
    const PREVIEW = 'preview';
    const LIVE    = 'live';
    
    private $_id;
    private $_model;
    private $_controller;
    abstract public function getData($locale=null);
    /**
     * Page constructor
     * @param type $id Page id
     * @param Shop $model Model owns the page
     * @param type $controller Controller of the page
     */
    public function __construct($id,$model,$controller)
    {
        $this->_id = $id;
        $this->_model = $model;
        $this->_controller = $controller;
    }
    public function getId()
    {
        return $this->_id;
    }    
    public function getModel()
    {
        return $this->_model;
    }    
    public function getController()
    {
        return $this->_controller;
    }   
    /**
     * Page name; Default to page id
     * @return type
     */
    public function getName($locale=null)
    {
        return $this->id;
    }
    /**
     * Get a particular page, attach theme based on controller
     * @return string
     */
    public function getPage()
    {
        $data = $this->data;//need to have temp holder to prevent load data twice
        return $this->controller->renderPartial($data['view'],$data['data'],true);
    }        
    /**
     * Default no dataprovider required
     * @return boolean
     */
    public function getDataProvider()
    {
        return false;
    }     
}
