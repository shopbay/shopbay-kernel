<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * OrdersModule
 * 
 * @author kwlok
 */
class OrdersModule extends SModule 
{ 
    /**
     * Init
     */    
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'orders.models.*',
            'orders.components.*',
        ]);
        // import module dependencies classes
        $this->setDependencies([
            'views'=>[
                //common views
                'orderproduct'=>'common.modules.orders.views.common._product',
                'merchantaddress'=>'common.modules.orders.views.common._address',
                'merchantshipping'=>'common.modules.orders.views.common._shipping',
                'merchantpayment'=>'common.modules.orders.views.common._payment',
                'merchantattachment'=>'common.modules.orders.views.common._attachments',
                'merchanthistory'=>'common.modules.orders.views.common._history',
            ],
        ]);         
    }
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.OrderManager',
                'model'=>['Order','ShippingOrder'],
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}
