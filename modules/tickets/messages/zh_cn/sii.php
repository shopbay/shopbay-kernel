<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    'Are you sure you want to close this ticket?'=>'您是否确定要关闭此支持票？',
    'All Tickets' => '全部支持票',
    'all'=>'全部',
    'Create'=>'创建',
    'Create Ticket'=>'创建支持票',
    'Closed Tickets' => '已关闭支持票',
    'Close Ticket'=>'关闭支持票',
    'close'=>'关闭',
    'closed'=>'已关闭',
    'Closed'=>'已关闭',
    'Enter your reply here..'=>'请从这里输入您的回复。。',
    'New Ticket'=>'新支持票',
    'open'=>'已提交',
    'Open Tickets' => '已提交支持票',
    'Reply'=>'回复',
    'Reply from {account}'=>'{account}回复',
    'Select Shop'=>'选择店铺',
    'Submit'=>'提交',
    'Submit Reply'=>'提交回复',
    'Support Tickets' => '支持票',
    'This ticket has been closed'=>'此支持票已关闭',
    'To re-open this ticket, simply reply a message! '=>'回复支持票即可重新开启！',
    'Ticket|Tickets'=> '支持票',
    'Ticket Reply'=>'支持票回复',
    'Ticket Reply Error'=>'回复错误',
    'Ticket "{name}" is closed successfully.'=>'支持票"{name}"已成功关闭。',
    //usability
    'This lists all the support tickets that you have raised.'=>'这里列出所有你已提交的支持票。',
    'This lists all the support tickets that are not yet closed.'=>'这里列出所有尚未关闭的支持票。',
    'This lists all the support tickets that are closed.'=>'这里列出所有已关闭的支持票。',
    'This lists all the support tickets that customers has submitted and pending reply.'=>'这里列出所有用户已提交待回复的支持票。',
]);
