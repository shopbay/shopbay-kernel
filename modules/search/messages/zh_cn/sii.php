<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    'Search shops, brands or products'=>'搜索店铺，品牌或产品',
    'Search brands or products'=>'搜索品牌或产品',
    'Search Results'=>'搜索结果',
    'Search request failed. Please try again.'=>'搜索执行失败. 请再试一次。',
]);
