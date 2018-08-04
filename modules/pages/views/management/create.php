<?php $this->module->registerFormCssFile();?>
<?php $this->module->registerCkeditor('page');?>
<?php
$this->breadcrumbs=$this->getBreadcrumbsData(Sii::t('sii','Create'));
$this->menu=[];

$this->getPage([
    'id'=>$this->modelType,
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> [
        'name'=> Sii::t('sii','Create Page'),
        'image'=> $this->pageOwner!=null?$this->pageOwner->getImageThumbnail(Image::VERSION_ORIGINAL,['style'=>'width:'.Image::VERSION_XSMALL.'px;']):null,
    ],
    'description'=>Sii::t('sii','Customize a page to your needs.'),
    'body'=>$this->renderPartial('common.modules.pages.views.management._form', ['model'=>$model],true),
]);
