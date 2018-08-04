<?php
$this->breadcrumbs=[
    Sii::t('sii','Messages'),
];
$this->menu = [
    ['id'=>'inbox','title'=>Sii::t('sii','Inbox'),'subscript'=>Sii::t('sii','inbox'), 'url'=>url('messages'),'linkOptions'=>['class'=>$this->action->id=='index'?'active':'']],    
    ['id'=>'unread','title'=>Sii::t('sii','Unread Messages'),'subscript'=>Sii::t('sii','unread'),'url'=>url('messages/unread'), 'linkOptions'=>['class'=>$this->action->id=='unread'?'active':'']],    
    ['id'=>'sent','title'=>Sii::t('sii','Sent Messages'),'subscript'=>Sii::t('sii','sent'), 'url'=>url('messages/sent'),'linkOptions'=>['class'=>$this->action->id=='sent'?'active':'']],    
];
    
$this->spageindexWidget(array_merge(['breadcrumbs'=>$this->breadcrumbs],
                                    ['menu' => $this->menu],
                                    ['flash' => $this->modelType],
                                    $config));
