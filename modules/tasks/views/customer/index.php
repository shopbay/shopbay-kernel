<?php
$this->breadcrumbs=array(
    Sii::t('sii','Tasks'),
);

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'heading'=> null,
    'linebreak'=> null,
    'body'=>$this->renderPartial($this->module->getView('tasks.tasklist'),array('role'=>$role),true),
));