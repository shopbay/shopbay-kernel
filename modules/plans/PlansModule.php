<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PlansModule
 *
 * @author kwlok
 */
class PlansModule extends SModule 
{
    /**
     * Init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            //nothing
        ]);
        
        // import module dependencies classes
        $this->setDependencies([
            //nothing
        ]);  
    }
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.PlanManager',
                'model'=>[
                    'Plan',
                    'Package',
                    'Subscription',
                ],
                'runMode'=>$this->serviceMode,
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}
