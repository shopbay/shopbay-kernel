<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    'Are you sure you want to delete this {object}?'=>'您是否确定要删除{object}？',
    'Are you sure you want to submit this tutorial?'=>'您是否确定要提交此教程？',
    'edit' => '更改',
    'Edit' => '更改',
    'Edit Tutorial' => '更改教程',
    'Edit {object}'=>'更改{object}',
    'Process History'=>'流程纪录',
    'Save' => '储存',
    'Select Difficulty'=> '选择程度',
    'Select Tags'=> '选择标签',
    'submit'=>'提交',
    'Submit Tutorial'=>'提交教程',
    'Title' => '题目',
    'Tutorials Management' => '教程管理',
    'Tutorial|Tutorials'=> '教程',
    'Tutorial Submission'=>'教程提交',
    'write' => '编写',
    'Write' => '编写',
    'Write Tutorial'=> '编写教程',
    'Write {object}'=>'编写{object}',
    '"{name}" is submitted successfully.'=>'"{name}"提交成功。',
    //usability
    'This lists all the tutorials you have contributed in the past. Do share more your {app} experience and give back to community.'=>'这里列出所有您过去所贡献的教程。分享更多您的{app}经验，并回馈社区。',
]);
