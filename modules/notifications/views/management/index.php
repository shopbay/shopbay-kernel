<?php
$this->breadcrumbs = array(
    Sii::t('sii','Notification Templates')=>array('index'),
);

$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'heading'=> array(
        'name'=> Sii::t('sii','Notification Templates'),
    ),
    'body'=>$this->renderPartial('_form',array(),true),
));
