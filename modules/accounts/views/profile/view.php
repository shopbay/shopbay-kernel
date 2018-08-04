<?php
$this->breadcrumbs=[
    Sii::t('sii','Account'),
];
$this->menu=[
    ['id'=>'process','title'=>Sii::t('sii','Manage Account'),'subscript'=>Sii::t('sii','account'), 'url'=>url('account')],
];

$this->widget('common.widgets.spage.SPage',[
    'id'=>'profile_page',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> false,
    'body'=>$this->renderPartial('_view_body',['model'=>$model],true),
    'sidebars'=>$this->getProfileSidebar(),
]);