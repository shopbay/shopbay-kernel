<?php 
$this->breadcrumbs=array(
	Sii::t('sii','Account')=>url('account/profile'),
	Sii::t('sii','Comments'),
);

$this->menu=array();

$this->spageindexWidget(array_merge(
    array('breadcrumbs'=>$this->breadcrumbs),
    array('menu'  => $this->menu),
    array('flash' => $this->modelType),
    array('description' => Sii::t('sii','This lists every comment that you had made in the past.')),
    array('sidebars' => $this->getProfileSidebar()),
    $config));   