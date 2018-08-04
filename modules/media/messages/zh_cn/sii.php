<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    'Are you sure you want to delete this {object}?'=>'您是否确定要删除{object}？',
    'Are you sure you want to activate this {object}?' => '您是否确定要上线{object}？',
    'Are you sure you want to deactivate this {object}?' => '您是否确定要下线{object}？',
    '"{name}" is activated successfully.'=>'"{name}"上线成功。',
    '"{name}" is deactivated successfully.'=>'"{name}"下线成功。',
    'Media'=>'媒体文件',
    'Media Error'=>'媒体文件上载错误',
    'upload'=>'上载',
    'Upload Media'=>'上载媒体文件',
    'File URL'=>'文件网址',
    'File size'=>'文件大小',
    'File Content'=>'文件内容',
    'Upload Time'=>'上载日期',
    'Download Media'=>'下载媒体文件',
    'Save'=>'储存',
    'Attached To'=>'使用物件',
    'Please select one media.'=>'请选择一个媒体文件。',
    'Media cannot be deactivated now. It is currently used by one or more online objects.'=>'媒体文件目前正在被一个或多个线上的物件使用中, 暂时无法下线。',
    'Media cannot be deleted now. It is associated with other objects.'=>'媒体文件正在被其他物件使用中, 暂时无法删除。',
    'You can upload multiple media files in one "Save".'=>'您可以一次上载多份媒体文件。',
    'Media with status {online} means everyone can access this media.'=>'媒体文件状态显示{online}代表任何人可访问此媒体文件。',
    '{name} is uploaded successfully'=>'您已成功上载{name}',
    '1#Click here to upload files|0#Click here to upload file'=>'点击这里上载媒体文件',
    'This lists all the media files you had uploaded in the past. Total {size}.'=>'这里列出所有您过去所上载过的媒体文件共 {size}。',
]);
