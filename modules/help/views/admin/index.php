<?php
$this->breadcrumbs=$this->getBreadcrumbs($helpfile);

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'body'=>$this->renderPartial('_body',array('helpfile'=>$helpfile),true),
));