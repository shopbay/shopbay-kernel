<?php
$this->breadcrumbs=$this->getBreadcrumbsData();

$this->menu=[
  //['id'=>'all','title'=>Sii::t('sii','All'), 'url'=>url('dashboard')],
];

//logic is controlled at DashboardControllerBehavior
$this->renderPageIndex(array_merge(
        ['breadcrumbs'=>$this->breadcrumbs],
        ['menu'  => $this->menu],
        ['flash' => 'Analytics'],
        ['hideHeading' => false],
        $this->getPageSidebar(),
        $config)
    );