<?php
/* @var $this ManagementController */
/* @var $model Message */
$this->breadcrumbs = $model->isSent(user()->getId())?array(
    Sii::t('sii','Messages')=>url('messages'),
    Sii::t('sii','Sent')=>url('messages/sent'),
    Sii::t('sii','View'),
):array(
    Sii::t('sii','Messages')=>url('messages'),
    Sii::t('sii','Inbox')=>url('messages'),
    Sii::t('sii','View'),
);

$this->menu=array(
    array('id'=>'reply','title'=>Sii::t('sii','Reply Message'),'subscript'=>Sii::t('sii','reply'), 'url'=>url('message/reply/'.$model->id), 'visible'=>$model->repliable(user()->getId())),
    array('id'=>'delete','title'=>Sii::t('sii','Delete Message'),'subscript'=>Sii::t('sii','delete'), 'visible'=>$model->deletable(),
            'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),
                                 'onclick'=>'$(\'.page-loader\').show();',
                                 'confirm'=>Sii::t('sii','Are you sure you want to delete this {object}?',array('{object}'=>strtolower($model->displayName()))))),
);

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
        'name'=> $model->getSubject(),
        'image'=> CHtml::image($this->getImage('mail_open.png'),'',array('style'=>'vertical-align:middle')),
    ),
    'body'=>$this->renderPartial('_view_body',array('model'=>$model),true),
));