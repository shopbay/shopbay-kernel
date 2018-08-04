<?php
$this->breadcrumbs=array(
    Sii::t('sii','Help Center')=>url('help'),
    Sii::t('sii','Tickets')=>url('tickets'),
    Sii::t('sii','View'),
);

$this->menu=$this->getPageMenu($model);

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => array(
        'name'=> $model->subject,
        'tag'=> $model->getStatusText(),
    ),
    'body'=>$this->renderPartial('_view_body', array('model'=>$model),true),
));
