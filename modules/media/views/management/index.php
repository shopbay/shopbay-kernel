<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Account')=>url('account/profile'),
    Sii::t('sii','Media'),
);

$this->menu=array(
    array('id'=>'import','title'=>Sii::t('sii','Upload Media'),'subscript'=>Sii::t('sii','upload'), 'url'=>array('create')),
);

$this->spageindexWidget(array_merge(
    array('breadcrumbs'=>$this->breadcrumbs),
    array('menu'  => $this->menu),
    array('flash' => $this->modelType),
    array('hideHeading' => false),
    array('description' => Sii::t('sii','This lists all the media files you had uploaded in the past. Total {size}.',['{size}'=>Media::getTotalSize(user()->getId(),true)])),
    array('sidebars' => $this->getProfileSidebar()),
    $config));
