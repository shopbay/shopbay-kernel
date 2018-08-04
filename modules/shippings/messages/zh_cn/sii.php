<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'day(s)'=>'天',
    'Fee Type'=>'运费类型',
    'Fields with <span class="required">*</span> are required.' => '所有 <span class="required">*</span> 的栏项必须填写。',
    'More information' => '查阅更多信息',
    'Name' => '名称',
    'Please add tier rule' => '请添加分层规则',
    'Please select tiered fee base' => '请选择分层运费计算方式',
    'Remove Option' => '删除规则',
    'Shippable shipping id attribute not set' => 'Shippable货运ID属性无定义',
    'Shipping Method' => '货运方式',
    'Shipping Tiers' => '分层运费设置',
    'shipping' => '货运',
    'Shippings' => '货运',
    'Shippings Management' => '货运管理',
    'Tiered Fee has no tier definition'=>'分层运费计算方式无内容',
    'Unauthorized Access' => '您无权限使用',
    'zone' => '区域',
    'Zones Management' => '区域管理',
]);
