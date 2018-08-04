<?php
return [
    'news/management/create'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$hasNewsLimitTierN),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('news'),
        'flashId'=>'News',
        'flashMessage'=>Sii::t('sii','You have hit news limit per month: {limit}.'),
    ],  
];