<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'brand' => '品牌分类',
    'Brands Management' => '品牌管理',
    'Brands' => '品牌',
    'category' => '通用分类',
    'Categories Management' => '通用分类管理',
    'Fields with <span class="required">*</span> are required.' => '所有 <span class="required">*</span> 的栏项必须填写。',
    'Missing Module' => '系统无法找到模组',
    'More information' => '查阅更多信息',
    'Name' => '名称',
    'No description'=>'无描述',
    'product' => '产品',
    'Products' => '产品',
    'Brand URL "{slug}" is already taken.'=>'品牌网址“{slug}”已被使用。',
    'Brand URL'=>'品牌网址',
]);
