<?php
return [
    'questions/management/index'=>[
        'checkBy'=>'ShopSubscriptionFilter',
        'permission'=>Feature::patternize(Feature::$manageQuestions),
        'shopFilter'=>true,
        'redirectUrlOnRejection'=>url('questions/management/serviceNotAvailable'),
        'flashId'=>'Question',
    ],   
];