<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ServiceException
 *
 * @author kwlok
 */
class ServiceException extends CException
{
    //CRUDS error
    const CREATE_ERROR     = 10001; 
    const READ_ERROR       = 10002; 
    const UPDATE_ERROR     = 10003; 
    const DELETE_ERROR     = 10004; 
    const SEARCH_ERROR     = 10005; 
    //ServiceManager error
    const VALIDATION_ERROR = 20001;
    const OPERATION_ERROR  = 20002;
    const WORKFLOW_ERROR   = 20003;
    const TRANSITION_ERROR = 20004;
    
    protected $name = 'Service Error';
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getType()
    {
        return get_class($this);
    }
    
}
