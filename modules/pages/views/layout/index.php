<?php
$this->breadcrumbs=$this->getBreadcrumbsData(Sii::t('sii','Page Editor'),$page->pageModel);
$this->menu=$this->getPageMenu($page->pageModel,'layout',[
        'createUrl'=>url('pages/management/create'),
        'viewUrl'=>$page->getPageViewUrl(),
        'updateUrl'=>url('pages/management/update/id/'.$page->pageModel->id),
        'deleteUrl'=>url('pages/management/delete/id/'.$page->pageModel->id),
        'layoutPreviewUrl'=>$page->getPagePreviewUrl(),
        'layoutEditUrl'=>$page->getPageEditUrl(),
    ]);

$this->getPage([
    'id'=>'page_layout_page',
    'cssClass'=>'bootstrap-page',//to enable support of bootstrap
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash' => $this->id,
    'heading'=> [
        'name'=> $page->pageModel->displayLanguageValue('title',user()->getLocale()),
    ],
    'description'=>$page->pageModel->displayLanguageValue('desc',user()->getLocale()),
    'body'=>$editor->run(true),
]);

$this->smodalWidget();  