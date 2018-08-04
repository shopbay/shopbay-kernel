<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PackageSections
 *
 * @author kwlok
 */
class PackageSections extends WorkflowSections
{
    public function prepareData()
    {
        //sections: Package Information
        $pkginfo = $this->controller->module->runControllerMethod('plans/package','getSectionsData',$this->model,true);//"true" for workflow view         
        foreach ($pkginfo as $section) {
            $this->sections->add($section);
        }
    }
}
