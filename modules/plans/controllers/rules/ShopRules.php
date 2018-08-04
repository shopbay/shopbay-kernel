<?php
return [
    'shops/management/apply'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'subscriptionRequired'=>true,
//        'permission'=>Feature::patternize(Feature::$hasShopLimitTierN),
//        'redirectUrlOnRejection'=>url('shop'),
        'flashId'=>'Shop',
        'flashMessage'=>Sii::t('sii','Please select a plan for your new shop.'),
//        'flashMessage'=>Sii::t('sii','You have hit shop limit: {limit}.'),
    ],  
    'shops/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'subscriptionRequired'=>true,
        //'queryParams'=>['shop'=>null],
        'flashTitle'=>Sii::t('sii','Create Shop'),
        'flashMessage'=>Sii::t('sii','Please select a plan for your new shop.'),
    ],          
    //Option: Below is the shop limit protection by creation
//    'shops/management/create'=>[
//        'permission'=>Feature::patternize(Feature::$hasShopLimitTierN),
//        'redirectUrlOnRejection'=>url('shop'),
//        'flashId'=>'Shop',
//        'flashMessage'=>Sii::t('sii','You have hit shop limit: {limit}.'),
//    ],      
    'shops/settings/brand'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'shopFilter'=>true,
        'permission'=>Feature::$hasCustomDomain,
        'redirectUrlOnRejection'=>url('shop/settings'),
        'flashId'=>'Shop',
    ],
    'analytics/management/index'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'shopFilter'=>true,
        'permission'=>Feature::$hasShopDashboard,
        'redirectUrlOnRejection'=>url('analytics/management/serviceNotAvailable'),
        'flashId'=>'Shop',//dummy used for ServiceNotAvailableAction
    ],    
    //Feature::$hasShopThemeLimitTierN check is handled at page level, @see DesignController::getThemeLimit()
    //Todo: Need to design new api (Feature Api) to retrieve feature details based on feature key
    //
    //Pending Feature::$hasCSSEditing
];