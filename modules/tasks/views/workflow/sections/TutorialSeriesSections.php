<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of TutorialSeriesSections
 *
 * @author kwlok
 */
class TutorialSeriesSections extends WorkflowSections
{
    public function prepareData()
    {
        //sections: Tutorial Series Information
        $seriesInfo = $this->controller->module->runControllerMethod('tutorials/series','getSectionsData',$this->model);            
        foreach ($seriesInfo as $section) {
            $this->sections->add($section);
        }
    }
}
