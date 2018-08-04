<?php $this->module->registerFormCssFile();?>
<?php
$this->breadcrumbs=$this->getBreadcrumbsData(Sii::t('sii','Update'),$model);
$this->menu=$this->getPageMenu($model->modelInstance,'update',['saveOnclick'=>'submitform(\'page_form\');']);

$this->getPage([
    'id'=>$this->modelType,
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> [
        'name'=> $model->displayLanguageValue('title',user()->getLocale()),
        'tag'=> $model->getStatusText(),
    ],
    'description'=>$model->displayLanguageValue('desc',user()->getLocale()),
    'body'=>$this->renderPartial('common.modules.pages.views.management._form', ['model'=>$model],true),
]);