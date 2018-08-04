<?php
return [
    'orders/merchant/index'=>[
        'checkBy'=>'SubscriptionFilter',//Currently this is not checked by ShopSubscriptionFilter as the menu is placed outside shop
        //'shopFilter'=>true,//Not required when use SubscriptionFilter
        'permission'=>Feature::$processOrders,
        'redirectUrlOnRejection'=>url('orders/merchant/serviceNotAvailable'),
        'flashId'=>'ShippingOrder',
    ],  
    'orders/merchantPO/index'=>[
        'checkBy'=>'SubscriptionFilter',//Currently this is not checked by ShopSubscriptionFilter as the menu is placed outside shop
        //'shopFilter'=>true,//Not required when use SubscriptionFilter
        'permission'=>Feature::$processOrders,
        'redirectUrlOnRejection'=>url('orders/merchantPO/serviceNotAvailable'),
        'flashId'=>'Order',
    ],     
    'post:shops/settings/orders?service='.Feature::$processOrders=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'shopFilter'=>true,
        'permission'=>Feature::$processOrders,
        'postModel'=>'Subscription',
        'postField'=>'service',
        'redirectUrlOnRejection'=>url('shop/settings/serviceNotAvailableJsonAction?Subscription=serviceNotAvailable&service='.Feature::$processOrders.'&returnUrl='.url('shop/settings/orders')),
        'flashId'=>'Shop',
    ],    
    'post:shops/settings/orders?service='.Feature::$customizeOrderNumber=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'shopFilter'=>true,
        'permission'=>Feature::$customizeOrderNumber,
        'postModel'=>'Subscription',
        'postField'=>'service',
        'redirectUrlOnRejection'=>url('shop/settings/serviceNotAvailableJsonAction?Subscription=serviceNotAvailable&service='.Feature::$customizeOrderNumber.'&returnUrl='.url('shop/settings/orders')),
        'flashId'=>'Shop',
    ],       
];

