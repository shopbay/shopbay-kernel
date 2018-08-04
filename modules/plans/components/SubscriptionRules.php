<?php
$basePath = Yii::getPathOfAlias('common.modules.plans.controllers.rules').DIRECTORY_SEPARATOR;
return array_merge(
    include $basePath.'ShopRules.php',
    include $basePath.'ProductRules.php',
    include $basePath.'InventoryRules.php', 
    include $basePath.'ShippingRules.php', 
    include $basePath.'TaxRules.php', 
    include $basePath.'NewsRules.php', 
    include $basePath.'QuestionRules.php', 
    include $basePath.'PaymentMethodRules.php',
    include $basePath.'OrderRules.php',
    include $basePath.'CustomerRules.php',
    include $basePath.'MarketingRules.php',
    include $basePath.'ChatbotRules.php',
    include $basePath.'PageRules.php'
);