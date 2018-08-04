<?php
return [
    'payments/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasPaymentMethodLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('payments'),
        'flashId'=>'PaymentMethod',
        'flashMessage'=>Sii::t('sii','You have hit payment method limit: {limit}.'),
    ],     
];