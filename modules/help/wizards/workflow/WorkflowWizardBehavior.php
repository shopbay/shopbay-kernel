<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of WorkflowWizardBehavior
 *
 * @author kwlok
 */
class WorkflowWizardBehavior extends CBehavior 
{
    private $_r;//user role
    private $_m;//model filter method
    /**
     * @return role id
     */
    public function setRole($role)
    {
        $this->_r = $role;
    }    
    /**
     * @return string role
     */
    public function getRole()
    {
        return $this->_r;
    }    
    /**
     * @return role id
     */
    public function setModelFilterMethod($method)
    {
        $this->_m = $method;
    }    
    /**
     * @return string model filter method
     */
    public function getModelFilterMethod()
    {
        return $this->_m;
    }    
}
