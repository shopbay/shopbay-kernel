<?php 
$this->widget('common.widgets.SDetailView', array(
    'id'=>'message_header',
    'data'=>$model,
    'columns'=>array(
        array(
            array('name'=>'sender','value'=>$model->senderName,'visible'=>$model->isNotSystemSender),
            array('name'=>'recipient','value'=>$model->recipientName,'visible'=>$model->hasMessageMetadata()),
            array('name'=>'send_time','value'=>$model->formatDatetime($model->send_time,true),'visible'=>$model->isNotSystemSender),
            array('name'=>'metadata','type'=>'raw','label'=>Sii::t('sii','Reference Link'),'value'=>CHtml::link($model->getReferenceName(),$model->getReferenceLink(user()->getId())),'visible'=>$model->hasMessageMetadata()),
        ),
    ),
    'htmlOptions'=>array('class'=>'detail-view'),
));

$this->widget('common.widgets.SDetailView', array(
    'data'=>$model,
    'columns'=>array(
        array(
            'content-column'=>$model->getContent(),
        ),
    ),
    'htmlOptions'=>array('class'=>'detail-view message'),
));   