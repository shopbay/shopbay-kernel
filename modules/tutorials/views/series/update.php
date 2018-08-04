<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerChosen();?>
<?php $this->getModule()->registerCkeditor('tutorial');?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Tutorial Series')=>url('tutorials/series'),
    Sii::t('sii','Update'),
);
$this->menu=$this->getPageMenu($model);

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => array(
        'name'=> $model->localeName(user()->getLocale()),
        'tag'=> $model->getStatusText(),
    ),
    'body'=>$this->renderPartial('_form', array('model'=>$model),true),
));