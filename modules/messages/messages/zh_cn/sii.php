<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'all' => '全部',
    'All Messages' => '全部短信',
    'Are you sure you want to delete this {object}?' => '您是否确定要删除{object}？',
    'Compose' => '编写',   
    'Content' => '内容',
    'delete' => '删除',
    'Delete Message' => '删除短信',
    'ID' => '编号',
    'Messages' => '短信',
    'Message not found'=>'系统查询无此短信',
    'Message|Messages' => '短信|短信',
    'Message with subject "{name}" is sent successfully'=>'您已成功发送短信，题目为"{name}"',
    'Missing Module' => '系统找不到模组',
    'Receive Time' => '收信日期',
    'Recipient' => '收件人',
    'Recipient not found'=>'系统查询无此收件人',
    'Reference Link'=>'参考链接',
    'reply' => '回复',
    'Reply Message' => '回复短信',
    'RE: {subject}'=>'回复：{subject}',
    'Order {order_no} Enquiry'=>'订单{order_no}询问',
    'Order not found'=>'系统查询无此订单',
    'Send' => '发送',
    'Send Time' => '发送日期',
    'Sender' => '发件人',
    'Sender not found'=>'系统查询无此发件人',
    'sent'=>'已发送',
    'Subject' => '题目',
    'Unauthorized Access' => '您无权限使用',
    'unread' => '未阅读',
    'Unread' => '未阅读',
    'Unread Messages' => '未阅读短信',
    'View' => '查阅',   
    'New Message' => '新短信',   
    '<p>--- Original Message ---</p><p>{content}</p>'=>'<p>－－－ 原始短信 －－－</p><p>{content}</p>',
]);
