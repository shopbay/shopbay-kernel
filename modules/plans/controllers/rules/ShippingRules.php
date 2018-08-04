<?php
return [
    'shippings/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasShippingLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('shippings'),
        'flashId'=>'Shipping',
        'flashMessage'=>Sii::t('sii','You have hit shipping limit: {limit}.'),        
    ], 
];