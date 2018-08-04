<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ProductsModule
 * Module placeholder for components, models, and behaviors classes commonly shared by other shopbay-apps
 * Note: No controllers and views 
 *
 * @author kwlok
 */
class ProductsModule extends SModule 
{
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'products.components.*',
            'products.models.*',
            'products.actions.*',
            'common.services.ProductImportManager',
        ]);
    }
    /**
     * Module display name
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Product|Products',[$mode]);
    }
    /**
    * @return ServiceManager
    */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=> [
                'class'=>'common.services.ProductManager',
                'model'=>['Category','Attribute','Product','ProductAttribute'],
                'htmlError'=>true,
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}