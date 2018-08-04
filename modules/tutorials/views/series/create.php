<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerChosen();?>
<?php $this->getModule()->registerCkeditor('tutorial');?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Tutorial Series')=>url('tutorials/series'),
    Sii::t('sii','Create'),
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => array(
        'name'=> Sii::t('sii','Create Tutorial Series'),
    ),
    'body'=>$this->renderPartial('_form', array('model'=>$model),true),
));