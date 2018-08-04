<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'Administrator Help' => '管理员使用指南',
    'Help' => '帮助',
    'Merchant Help' => '商家使用指南',
    'Orders' => '订单',
    'Customers' => '客户',
    'support'=>'支持中心',
    'Support Center'=>'支持中心',
]);
