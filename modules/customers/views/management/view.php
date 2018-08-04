<?php
$this->breadcrumbs=[
    Sii::t('sii','Customers')=>url('customers'),
    Sii::t('sii','View'),
];

$this->menu=$this->getPageMenu($model);

$this->widget('common.widgets.spage.SPage', [
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => [
        'name'=> $model->alias,
        'image'=>$model->getImageThumbnail(),
        'subscript'=>Sii::t('sii','Record since').' '.$model->formatDatetime($model->create_time,false),
        'superscript'=> $model->isRegistered?$model->registeredTag:'',
     ],
    'body'=>$this->renderPartial('_profile',['model'=>$model],true).
            $this->renderPartial('_view_body', ['model'=>$model],true),
    'sections'=>$this->getSectionsData($model),
]);
