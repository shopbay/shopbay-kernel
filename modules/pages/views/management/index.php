<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=$this->getBreadcrumbsData();

$this->menu=$this->getPageMenu(null,'index',[
    ['id'=>'page','title'=>Sii::t('sii','Create Page'),'subscript'=>Sii::t('sii','create'), 'url'=>$this->getCreatePageUrl()],    
]);
    
$this->getPageIndex(array_merge(
    ['breadcrumbs'=>$this->breadcrumbs],
    ['menu' => $this->includePageFilter ? [] : $this->menu], //show menu if page filter is not set
    ['flash' => $this->modelType],
    ['hideHeading' => false],
    ['description' => Sii::t('sii','Manage and customize web pages layout and content.')],
    $this->getPageSidebar($this->includePageFilter,$this->menu),
    $config));
