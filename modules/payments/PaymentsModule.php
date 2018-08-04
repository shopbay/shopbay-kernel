<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PaymentsModule
 *
 * @author kwlok
 */
class PaymentsModule extends SModule 
{
    /**
     * Init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'payments.models.*',
            'payments.behaviors.*',
            'payments.components.*',
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
                'class'=>'common.services.PaymentMethodManager',
                'model'=>'PaymentMethod',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}