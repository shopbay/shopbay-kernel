<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerChosen();?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Help Center')=>url('help'),
    Sii::t('sii','Tickets')=>url('tickets'),
    Sii::t('sii','Create'),
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => array(
        'name'=> Sii::t('sii','New Ticket'),
    ),
    'body'=>$this->renderPartial('_form', array('model'=>$model),true),
));