<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Media')=>url('media/management/index'),
    Sii::t('sii','View'),
);

$this->menu=$this->getPageMenu($model);

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => array(
        'name'=> $model->name,
        'image'=> $model->icon,
        'tag'=> $model->getStatusText(),
    ),
    'description' => Sii::t('sii','Media with status {online} means everyone can access this media.',array('{online}'=>Process::getHtmlDisplayText(Process::MEDIA_ONLINE))),
    'body'=>$this->renderPartial('_view_body', array('model'=>$model),true),
));
