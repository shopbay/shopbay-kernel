<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=[
    Sii::t('sii','System Emails'),
];
$this->menu=[];
    
$this->spageindexWidget(array_merge(
    ['breadcrumbs'=>$this->breadcrumbs],
    ['menu'  => $this->menu],
    ['flash' => $this->modelType],
    ['hideHeading' => false],
    ['description' => Sii::t('sii','This lists all the emails system had sent out in the past, or pending sent.')],
    $config));
