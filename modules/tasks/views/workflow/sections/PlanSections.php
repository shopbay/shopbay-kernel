<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PlanSections
 *
 * @author kwlok
 */
class PlanSections extends WorkflowSections
{
    public function prepareData()
    {
        //sections: Plan Information
        $planinfo = $this->controller->module->runControllerMethod('plans/management','getSectionsData',$this->model);            
        foreach ($planinfo as $section) {
            $this->sections->add($section);
        }
    }
}
