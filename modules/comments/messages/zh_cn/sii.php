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
    'Are you sure you want to delete comment for' => '您是否确定要删除此评论',
    'Are you sure you want to delete this {object}?' => '您是否确定要删除{object}？',
    'Author not found' => '系统找不到作者',
    'Change' => '更改',
    'Comment' => '评论',
    'Comment By' => '评论发表者',
    'Comment On' => '评论课题',
    'Comments' => '评论',
    'Comment|Comments' => '评论|评论',
    'Create Time' => '创建日期',
    'Delete Comment' => '删除评论',
    'ID' => '编号',
    'Missing Module' => '系统找不到模组',
    'More Information' => '更多信息',
    'Obj' => '物件',
    'Obj Type' => '物件类',
    'Object ID cannot be blank' => '物件编码不能空白',
    'Object type cannot be blank' => '物件类不能空白',
    'promotion' => '优惠',
    'Post' => '提交',
    'Rating' => '评级',
    'Save Comment' => '储存意见',
    'The requested page does not exist' => '您所查询的网页不存在',
    'Update Comment' => '更新评论',
    'Update Time' => '更新日期',
    'View Comment' => '查看评论',
    'Write a comment' => '发表评论',
    'Write a comment for this {target}'=>'我想对此{target}发表评论',
    'n==1#{n} Comment|n>1#{n} Comments'=>'n==1#{n} 评论|n>1#{n} 评论',    
    '{object} Creation' => '{object}创建',
    '{object} is posted successfully.' => '{object}提交成功。',
    'This lists every comment that you had made in the past.'=>'这里列出过去你所发表过的评论。',
]);
