<?php
$this->breadcrumbs=array(
    Sii::t('sii','System Emails')=>url('notifications/emailTrail/index'),
    Sii::t('sii','View'),
);

$this->menu=[];

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => [
        'name'=> $model->data->subject,
        'tag'=> $model->getHtmlStatusTag(),
    ],
    'body'=>$this->renderPartial('_view_body', array('model'=>$model),true),
));
