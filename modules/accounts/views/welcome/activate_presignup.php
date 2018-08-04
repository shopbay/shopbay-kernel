<?php
$this->breadcrumbs=[
    Sii::t('sii','Home'),
];
$this->menu=[];

$this->widget('common.widgets.spage.SPage',[
    'id'=>'activate_presignup',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash' => $this->getFlashes(),
    'heading'=> array(
        'name'=> Sii::t('sii','Welcome!'),
    ),
    'body'=>$this->renderPartial('activate',[],true),
    'sidebars' => $this->showSidebar()?array(
            SPageLayout::COLUMN_RIGHT=>array(
                'content'=>$this->renderPartial('_sidebar',array(
                                'messages'=>$this->getRecentMessages(),
                                'news'=>$this->getRecentNews(),
                                'activity'=>$this->getRecentActivity(),
                            ),true),
                'cssClass'=>SPageLayout::WIDTH_25PERCENT),
        ):null,
]);