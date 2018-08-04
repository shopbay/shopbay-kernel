<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ItemsModule
 *
 * @author kwlok
 */
class ItemsModule extends SModule 
{        
        
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'items.models.*',
        ]);
    }
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.ItemManager',
                'model'=>'Item',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
}
