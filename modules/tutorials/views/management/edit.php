<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerCkeditor('tutorial');?>
<?php $this->getModule()->registerChosen();?>
<?php
$this->breadcrumbs=[
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Tutorials')=>url('tutorials'),
    Sii::t('sii','Edit'),
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
    'body'=>$this->renderPartial('_form', ['model'=>$model],true),
]);