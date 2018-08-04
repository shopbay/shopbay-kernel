<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of TutorialSections
 *
 * @author kwlok
 */
class TutorialSections extends WorkflowSections
{
    public function prepareData()
    {
        //sections: Tutorial Information
        $tutorialInfo = $this->controller->module->runControllerMethod('tutorials/management','getSectionsData',$this->model);            
        foreach ($tutorialInfo as $section) {
            $this->sections->add($section);
        }
    }
}
