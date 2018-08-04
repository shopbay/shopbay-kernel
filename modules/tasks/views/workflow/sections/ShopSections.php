<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ShopSections
 *
 * @author kwlok
 */
class ShopSections extends WorkflowSections
{
    public function prepareData()
    {
        //sections: Shop Information
        $shopInfo = $this->controller->module->runControllerMethod('shops/management','getSectionsData',$this->model);            
        foreach ($shopInfo as $section) {
            $this->sections->add($section);
        }
    }
}
