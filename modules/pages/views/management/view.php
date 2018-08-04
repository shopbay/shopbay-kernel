<?php
$this->breadcrumbs=$this->getBreadcrumbsData(Sii::t('sii','View'),$model);

$this->menu=$this->getPageMenu($model,$this->action->id,[
    'activateUrl'=>$this->getPageUrl('activate',['Page[id]'=>$model->id]),
    'deactivateUrl'=>$this->getPageUrl('deactivate',['Page[id]'=>$model->id]),
]);

$this->getPage([
    'id'=>$this->modelType,
    'cssClass'=>'bootstrap-page',//to enable support of bootstrap
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> [
        'name'=> $model->displayLanguageValue('title',user()->getLocale()),
        'tag'=> $model->getStatusText(),
    ],
    'description'=>$model->displayLanguageValue('desc',user()->getLocale()),
    'body'=>$this->renderPartial('common.modules.pages.views.management._view_body', ['model'=>$model],true),
]);

