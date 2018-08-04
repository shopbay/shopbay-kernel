<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TransitionBehavior
 *
 * @author kwlok
 */
class TransitionBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of the status to indicate 'Active'. Defaults to 'ON;'
    */
    public $activeStatus = 'ON;';
    /**
    * @var mixed The name of the status to indicate 'Inactive'. Defaults to 'OFF;'
    */
    public $inactiveStatus = 'OFF;';    
    
    public function active() 
    {
        return $this->getOwner()->status($this->activeStatus);
    }  
    
    public function inactive()
    {
        return$this->getOwner()->status($this->inactiveStatus);
    }  
    
    public function activable() 
    {
        if (is_array($this->inactiveStatus))
            return in_array ($this->getOwner()->status, $this->inactiveStatus);
        else
            return $this->getOwner()->status==$this->inactiveStatus;
    }
    
    public function deactivable()
    {
        if (is_array($this->activeStatus))
            return in_array ($this->getOwner()->status, $this->activeStatus);
        else
            return $this->getOwner()->status==$this->activeStatus;
    }
    
    public function online()
    {
        return $this->getOwner()->deactivable();
    }
    
    public function offline()
    {
        return $this->getOwner()->activable();
    }
}
