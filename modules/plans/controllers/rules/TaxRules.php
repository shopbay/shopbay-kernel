<?php
return [
    'taxes/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasTaxLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('taxes'),
        'flashId'=>'Tax',
        'flashMessage'=>Sii::t('sii','You have hit tax limit: {limit}.'),
    ], 
];