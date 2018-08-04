<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerCkeditor('message');?>
<?php 
$this->breadcrumbs=array(
	Sii::t('sii','Messages')=>url('messages'),
        Sii::t('sii','Compose'),
);

$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'id'=>$this->modelType,
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
        'name'=> Sii::t('sii','New Message'),
    ),
    'body'=>$this->renderPartial('_form', array('model'=>$model),true),
));
