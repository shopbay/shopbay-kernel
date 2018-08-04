<?php
return [
    'post:shops/settings/chatbot?service='.Feature::$integrateFacebookMessenger=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'shopFilter'=>true,
        'permission'=>Feature::$integrateFacebookMessenger,
        'postModel'=>'Subscription',
        'postField'=>'service',
        'redirectUrlOnRejection'=>url('shop/settings/serviceNotAvailableJsonAction?Subscription=serviceNotAvailable&service='.Feature::$integrateFacebookMessenger.'&returnUrl='.url('shop/settings/chatbot')),
        'flashId'=>'Shop',
    ],       
];

