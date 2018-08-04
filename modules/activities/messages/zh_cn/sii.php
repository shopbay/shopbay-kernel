<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'Account' => '账号',
    'Activities' => '活动',
    'Activity Validation Error' => '活动验证错误',
    'Activity|Activities' => '活动|所有活动',
    'All' => '全部',
    'Create Time' => '创建日期',
    'Comment' => '评论',
    'Description' => '说明',
    'Event' => '事件',
    'ID' => '编号',
    'Icon Url' => '图标网址',
    'Item' => '商品',
    'Object ID' => '物件编号',
    'Object Type' => '物件类',
    'Object Url' => '物件网址',
    'Order' => '订单',
    'Question' => '问题',
    'Like' => '喜欢',
    'Record Activity Error - {message}' => '活动记录错误 - {message}',
]);
