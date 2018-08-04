<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of RightsFilterBehavior
 *
 * @author kwlok
 */
class RightsFilterBehavior extends CBehavior 
{
    private $_skipFilter = false;
    public $whitelistActions = [];
    public $whitelistModels  = [];
    public $whitelistMethod  = 'allowGuestTaskWorkflow';
    public $whitelistTasks   = [];//@see CustomerAuthManager
    /**
     * @param Callable
     * @return boolean True if controller action can be whitelist, and skip rights fliter
     */
    public function checkWhitelist($findModelMethod)
    {
        Yii::app()->authManager->setExemptedTasks($this->whitelistTasks);
        $model = $findModelMethod();//expects to return shop model 
        if ($model==null || !in_array(get_class($model), $this->whitelistModels)){
            logWarning(__METHOD__.' No!', request()->getRequestUri());
            return;
        }
        if ($model->{$this->whitelistMethod}()){
            $this->_skipFilter = true;
            foreach ($this->whitelistActions as $action) {
                $this->getOwner()->rightsFilterActionsExclude[] = $action;
                logTrace(__METHOD__.' ok for '.$action, request()->getRequestUri());
            }
        }
        else
            logInfo(__METHOD__.' fails to execute '.get_class($model).'->'.$this->whitelistMethod, request()->getRequestUri());
    }
    /**
     * @return boolean if rights filter is enforced
     */
    public function skipRightsFilter()
    {
        return $this->_skipFilter;
    }
}
