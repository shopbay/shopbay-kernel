<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'Adjust' => '调整',
    'Fields with <span class="required">*</span> are required.' => '所有 <span class="required">*</span> 的栏项必须填写。',
    'Inventory History' => '库存记录',
    'Inventory Management' => '库存管理',
    'More information' => '查阅更多信息',
    'Product'=> '产品',
    'Product Name'=> '产品名称',
    'The requested page does not exist' => '您所查找的网页不存在',
    'Unauthorized Access' => '您无权限使用',
    'Validation Error' => '验证错误',
]);
