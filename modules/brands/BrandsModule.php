<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BrandModule
 * Module placeholder for components, models, and behaviors classes commonly shared by other shopbay-apps
 * Note: No controllers and views 
 *
 * @author kwlok
 */
class BrandsModule extends SModule 
{
        
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'brands.models.*',
            'brands.behaviors.*',
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
                'class'=>'common.services.BrandManager',
                'model'=>'Brand',
                'htmlError'=>true,
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}
