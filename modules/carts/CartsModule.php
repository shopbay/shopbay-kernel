<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of CartsModule
 *
 * @author kwlok
 */
class CartsModule extends SModule 
{
    /**
     * Init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'carts.models.*',
            'carts.components.*',
            'carts.behaviors.*',
            'carts.models.*',
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
                'class'=>'common.services.CartManager',
                'model'=>'Cart',
                'saveTransition'=>false,
            ],
        ]);
        // import module dependencies classes
        $this->setDependencies([
            'views'=>[
                //common views
                'empty'=>'common.modules.carts.views.management._empty',
                'buttons'=>'common.modules.carts.views.management._buttons',
                'keyvalue'=>'common.modules.carts.views.management._key_value',
            ],
        ]);          
        return $this->getComponent('servicemanager');
    }
    
}