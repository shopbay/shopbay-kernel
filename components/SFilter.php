<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SFilter
 *
 * @author kwlok
 */
class SFilter extends CApplicationComponent
{
    /**
     * The filter rules to be attached to controllers
     * @see AuthenticatedController::filters()
     * @var array
     */
    public $rules = [];
    /**
     * Add rule to filters
     * @param $rule
     */
    public function addRule($rule)
    {
        $this->rules[] = $rule;
        logTrace(__METHOD__.' new rule added',$rule);
    }
}