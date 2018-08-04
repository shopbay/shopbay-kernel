<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.rights.components.RDbAuthManager');
/**
 * Description of SAuthManager
 *
 * @author kwlok
 */
class SAuthManager extends RDbAuthManager
{
    private $_exemptedTasks = []; 
   
    public function getExemptedTasks()
    {
        return $this->_exemptedTasks;
    }    
    
    public function setExemptedTasks($tasks)
    {
        $this->_exemptedTasks = $tasks;
        logTrace(__METHOD__,$this->_exemptedTasks);
    }
}
