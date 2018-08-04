<?php
$this->breadcrumbs = [
    Sii::t('sii','Account'),
    Sii::t('sii','Notifications'),
];

$this->menu = [
    ['id'=>'notify','title'=>Sii::t('sii','Manage Notifications'),'subscript'=>Sii::t('sii','notification'), 'url'=>url('notifications'),'linkOptions'=>['class'=>'active'],'visible'=>user()->isAuthorizedActivated],
];

$this->widget('common.widgets.spage.SPage',[
    'id'=>'account_page',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash' => $this->id,
    'heading'=> [
        'name'=> Sii::t('sii','Notifications'),
    ],
    'description'=>Sii::t('sii','Subscribe to the notifications that you would like to receive'),
    'body'=>$this->renderPartial('_notifications',[],true),
    'sidebars' => $this->getProfileSidebar(user()->getAccountMenu(),SPageLayout::WIDTH_15PERCENT),
]);