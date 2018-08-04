<?php
$this->breadcrumbs = array(
    Sii::t('sii','Notification Templates')=>array('index'),
    $this->getTemplateType($key),
    Notification::getTemplateName($key),
);

$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'linebreak'=>false,
    'heading'=> false,
    'body'=>$this->getTemplateView($key),
));