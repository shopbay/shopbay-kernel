<?php
return [ 
    'pages/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasPageLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('pages'),
        'flashId'=>'Page',
        'flashMessage'=>Sii::t('sii','You have hit page limit: {limit}.'),
    ],
];