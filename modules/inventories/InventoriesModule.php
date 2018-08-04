<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of InventoriesModule
 * Module placeholder for components, models, and behaviors classes commonly shared by other shopbay-apps
 * Note: No controllers and views 
 * 
 * @author kwlok
 */
class InventoriesModule extends SModule 
{
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'inventories.models.*',
            'inventories.behaviors.*',
        ]);
    }
    /**
     * Module display name
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Inventory|Inventories',[$mode]);
    }     
    /**
    * @return ServiceManager
    */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.InventoryManager',
                'model'=>['Inventory'],
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}