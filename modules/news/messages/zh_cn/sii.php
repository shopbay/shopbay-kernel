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
    'Content' => '内容',
    'Fields with <span class="required">*</span> are required.' => '所有 <span class="required">*</span> 的栏项必须填写。',
    'Headline' => '新闻标题',
    'ID' => '编号',
    'More Information' => '查阅更多信息',
    'News' => '新闻',
    'News Blog' => '新闻博客',
    'News|News' => '新闻|新闻',
    'Release Date'=>'发布日期',
    'Shop' => '店铺',
    'Status' => '状态',
]);
