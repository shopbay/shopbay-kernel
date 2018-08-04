<?php
return [
    'customers/management/index'=>[
        'checkBy'=>'SubscriptionFilter',//Currently this is not checked by ShopSubscriptionFilter as the menu is placed outside shop
        //'shopFilter'=>true,//Not required when use SubscriptionFilter
        'permission'=>Feature::$manageCustomers,
        'redirectUrlOnRejection'=>url('customers/management/serviceNotAvailable'),
        'flashId'=>'Customer',
    ],  
];

