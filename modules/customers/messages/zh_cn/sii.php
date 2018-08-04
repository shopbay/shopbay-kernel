<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'Address 2' => '地址二',
    'Customers Management' => '客户管理',
    'Create' => '创建',
    'Create Customer'=> '创建客户',
    'Customer|Customers'=> '客户',
    'Customers'=> '客户',
    'Customer Name'=> '客户名称',
    'Alias Name'=> '别名',
    'First Name'=> '名字',
    'Last Name'=> '姓',
    'Gender'=> '性别',
    'M'=> '男',
    'F'=> '女',
    'Last Visited Shop'=>'最近拜访店铺',
    'Last Order'=>'最新订单',
    'Location'=>'位置',
    'Recent Orders'=>'最近购买订单',
    'Record since'=>'客户记录始于',
    'Notes'=> '备注',
    'not available'=>'无数据',
    'Tags'=> '标签',
    'Update' => '更新',
    'Update Customer' => '更新客户',
    'Save' => '储存',
    'Total Orders'=>'订单总数',
    'Total Spent'=>'总消费',
    'Visited Shops'=>'拜访过的店铺',
    'You can enter multiple tags using comma as separator. For example, "prospects, VIP" etc.'=>'您可用逗号来做多个标签的区隔，例如 “潜在目标，重要客户” 等。',
    'You can enter any extra information you want to keep track for this customer.'=>'您可在这里备注客户其他信息。',
    'Your customers are upmost important. Every registered customer or any guests who send you order will be automatically added into this database. Manage and know your customers better and reward them, e.g. loyal customers who buy most stuff from you.'=>'您的客户至上重要。每一个注册客户或无账号向您发送订单的客户将自动被添加到这个数据库。管理及更多了解您的客户并给予奖励，尤其是常光顾您的店铺并购买最多商品的忠实客户。',
    'You can track also potential customers and keep their profiles for future use.'=>'您亦可以记录和跟踪潜在客户，并保持他们的个人资料以备将来使用。',
    'Enter any address'=>'输入任何地址',
    'Enter any tags'=>'输入任何标签',
    'Enter any notes'=>'输入任何备注',
    'Enter any customer name'=>'输入任何客户名称',
    'Registered Account' => '注册账号',
    'Yes' => '是',
    'No' => '否',
]);
