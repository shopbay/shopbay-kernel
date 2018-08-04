<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerTaskScript(true);?>
<?php $this->getModule()->registerAnalyticsScript();?>
<?php $this->getModule()->registerSUploadScript();?>
<?php         
$this->breadcrumbs = [
	Sii::t('sii','Home'),
];
$this->menu=[];

$this->spageindexWidget(array_merge(
    ['breadcrumbs'=>$this->breadcrumbs],
    ['menu'  => $this->menu],
    ['flash' => $this->getFlashes()],
    ['sidebars' => $this->showSidebar()?[
            SPageLayout::COLUMN_RIGHT=>[
                'content'=>$this->renderPartial('_sidebar',[],true),
                'cssClass'=>SPageLayout::WIDTH_20PERCENT,
            ],
        ]:null],
    $config));
