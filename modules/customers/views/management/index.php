<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=array(
	Sii::t('sii','Customers'),
);

$this->menu=array(
    array('id'=>'create','title'=>Sii::t('sii','Create Customer'),'subscript'=>Sii::t('sii','create'), 'url'=>array('create')),    
);
    

$this->spageindexWidget(array_merge(
    ['breadcrumbs'=>$this->breadcrumbs],
    ['flash' => $this->modelType],
    ['description' => Sii::t('sii','Your customers are upmost important. Every registered customer or any guests who send you order will be automatically added into this database. Manage and know your customers better and reward them, e.g. loyal customers who buy most stuff from you.')],
    ['hideHeading' => false],
    //['menu'  => $this->menu],
    ['sidebars'=>$this->getPageFilterSidebarData()],
    $config)
);
