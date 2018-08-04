<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShippingsModule
 *
 * @author kwlok
 */
class ShippingsModule extends SModule 
{
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'shippings.models.*',
            'shippings.components.*',
        ]);
    }
    /**
     * Module display name
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Shipping|Shippings',[$mode]);
    }    
    /**
    * @return ServiceManager
    */
    public function getServiceManager()
    {
        // Set the required components.
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.ShippingManager',
                'model'=>['Shipping','Zone'],
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}