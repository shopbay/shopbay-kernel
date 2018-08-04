<?php $this->getModule()->registerFormCssFile();?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Media')=>url('media/management/index'),
    Sii::t('sii','Upload'),
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage', array(
    'id'=>$this->modelType,
    'breadcrumbs' => $this->breadcrumbs,
    'menu' => $this->menu,
    'flash' => get_class($model),
    'heading' => array(
        'name'=> Sii::t('sii','Upload Media'),
    ),
    'description' => Sii::t('sii','You can upload multiple media files in one "Save".'),
    'body'=>$this->renderPartial('_form', array('model'=>$model),true),
));