<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Likes'),
);
    
$this->spageindexWidget(array_merge(
    array('breadcrumbs'=>$this->breadcrumbs),
    array('flash' => $this->id),
    array('sidebars' => $this->getProfileSidebar()),
    $config));
