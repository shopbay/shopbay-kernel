<?php 
$this->widget('common.widgets.SDetailView', array(
    'id'=>'ticket_view',
    'data'=>$model,
    'columns'=>array(
        array(
            array('name'=>'account_id','type'=>'raw','value'=>$model->account->getAvatar(Image::VERSION_SMALL).' '.$model->account->name,'visible'=>user()->hasRole(Role::ADMINISTRATOR)),
            array('name'=>'shop_id','value'=>$this->getShopName($model),'visible'=>$this->module->enableShopField),
            array('name'=>'create_time','value'=>$model->formatDatetime($model->create_time,true)),
            array('type'=>'raw','cssClass'=>'line-break','value'=>''),    
            array('type'=>'raw','cssClass'=>'content-data','value'=> Helper::purify($model->content)),    
        ),
    ),
));

$this->renderPartial('_view_reply',array('data'=>$this->getAllReplyData($model)));

$this->renderPartial('_replyform',array('model'=>$this->getReplyForm($model)));