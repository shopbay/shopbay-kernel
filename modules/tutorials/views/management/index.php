<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Tutorials'),
);

$this->menu=array(
    array('id'=>'write','title'=>Sii::t('sii','Write Tutorial'),'subscript'=>Sii::t('sii','write'), 'url'=>array('write')),    
    array('id'=>'series','title'=>Sii::t('sii','Tutorial Series'),'subscript'=>Sii::t('sii','series'), 'url'=>url('tutorials/series'),'visible'=>user()->hasRoleTask(Task::TUTORIAL_SERIES)),
);
    
$this->spageindexWidget(array_merge(
    array('breadcrumbs'=>$this->breadcrumbs),
    array('menu'  => $this->menu),
    array('flash' => $this->modelType),
    array('hideHeading' => false),
    array('description' => Sii::t('sii','This lists all the tutorials you have contributed in the past. Do share more your {app} experience and give back to community.',array('{app}'=>param('SITE_NAME')))),
    array('sidebars' => $this->getProfileSidebar()),
    $config));
