<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'All' => '全部',
    'Account' => '账号',
    'Create Time' => '创建日期',
    'Campaign' => '促销活动',
    'I like' => '我喜欢',
    'I unlike' => '取消',    
    'I like this {item}' => '我喜欢此{item}',
    'ID' => '编号',
    'Missing Module' => '系统无法找到模组',
    'Object' => '物件',
    'Object Name' => '物件名称',
    'Object Picture Url' => '物件图片网址',
    'Object Source ID' => '物件来源编号',
    'Object Type' => '物件类',
    'Object Url' => '物件网址',
    'Oops, but you can always like it again if you change your mind.' => '抱歉，如果您改变主意您还是可以重新选择喜欢此商品。',
    'Oops, it seems we have problem save your liked items' => '抱歉，系统无法储存你的喜欢商品',
    'Status' => '状态',
    'Like' => '喜欢',
    'Likes Error' => '喜欢系统错误',
    'Likes Message' => '喜欢消息',
    'Likes' => '喜欢',
    'Like|Likes' => '喜欢|喜欢',
    'Thanks for your support again! In fact, you have liked {object} before.' => '谢谢您的支持！您可能没留意您已喜欢过此{object}。',
    'Unauthorized Access' => '你无权限使用',
    'Dislike {object}' => '不喜欢{object}',
    'Update Time' => '编辑日期',
    'n<=1#{n} Like|n>1#{n} Likes'=>'{n} 喜欢',    
    '{object} is liked successfully' => '您已成功喜欢{object}。',
    '{object} is disliked successfully' => '您已成功取消喜欢{object}。',    
    //usability
    'This lists everything that you have liked. If you change your mind now, you can dislike any one of them by clicking again on "Heart".'=>'这里列出你所喜欢的一切。若您现在改变主意，您可通过再次点击“心”图标来表示不喜欢他们中的任何一个。',
    'This lists every shop that you have liked.'=>'这里列出你所喜欢的店铺。',
    'This lists every product that you have liked.'=>'这里列出你所喜欢的产品。',
    'This lists every campaign that you have liked.'=>'这里列出你所喜欢的促销活动。',
]);
