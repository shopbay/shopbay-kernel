<?php
$this->breadcrumbs=[
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Tutorials')=>url('tutorials'),
    Sii::t('sii','View'),
];

$this->menu=$this->getPageMenu($model);

$this->widget('common.widgets.spage.SPage', [
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => [
        'name'=> $model->localeName(user()->getLocale()),
        'tag'=> $model->getStatusText(),
    ],
    'body'=>$this->renderPartial('_view_body', ['model'=>$model],true),
    'sections'=>$this->getSectionsData($model),
]);
