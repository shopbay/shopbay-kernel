<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of BillingsModule
 *
 * @author kwlok
 */
class BillingsModule extends SModule 
{
    public $paymentGateway;    
    
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'billings.components.*',
            'billings.models.*',
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
                'class'=>'common.services.BillingManager',
                'model'=>['Billing'],
                'paymentGateway'=>$this->paymentGateway,
            ],
        ]);
        return $this->getComponent('servicemanager');
    }

}
