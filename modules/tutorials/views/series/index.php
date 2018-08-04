<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Tutorial Series'),
);

$this->menu=array(
    array('id'=>'create','title'=>Sii::t('sii','Create Tutorial Series'),'subscript'=>Sii::t('sii','create'), 'url'=>array('create')),    
    array('id'=>'tutorials','title'=>Sii::t('sii','Tutorials'),'subscript'=>Sii::t('sii','tutorials'), 'url'=>url('tutorials'),'visible'=>user()->hasRoleTask(Task::TUTORIALS)),
);
    
$this->spageindexWidget(array_merge(
    array('breadcrumbs'=>$this->breadcrumbs),
    array('menu'  => $this->menu),
    array('flash' => $this->modelType),
    array('hideHeading' => false),
    array('description' => Sii::t('sii','This lists all the tutorial series you have created in the past.')),
    array('sidebars' => $this->getProfileSidebar()),
    $config));
