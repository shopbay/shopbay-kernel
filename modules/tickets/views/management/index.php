<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=array(
    Sii::t('sii','Help Center')=>url('help'),
    Sii::t('sii','Tickets'),
);
$this->menu=array(
    array('id'=>'ticket','title'=>Sii::t('sii','Create Ticket'),'subscript'=>Sii::t('sii','create'), 'url'=>array('create'),'linkOptions'=>array('class'=>'primary-button'),'visible'=>!user()->isAdmin),    
    array('id'=>'ticket','title'=>Sii::t('sii','All Tickets'),'subscript'=>Sii::t('sii','all'), 'linkOptions'=>array('id'=>'all_items','class'=>$this->getPageMenuCssClass('all'),'onclick'=>'filter("'.$this->route.'","'.$this->modelType.'","all")')),
    array('id'=>'ticket','title'=>Sii::t('sii','Open Tickets'),'subscript'=>Sii::t('sii','open'), 'linkOptions'=>array('id'=>'submitted_items','class'=>$this->getPageMenuCssClass('submitted'),'onclick'=>'filter("'.$this->route.'","'.$this->modelType.'","submitted")'),'visible'=>!user()->isAdmin),
    array('id'=>'ticket','title'=>Sii::t('sii','Closed Tickets'),'subscript'=>Sii::t('sii','closed'), 'linkOptions'=>array('id'=>'closed_items','class'=>$this->getPageMenuCssClass('closed'),'onclick'=>'filter("'.$this->route.'","'.$this->modelType.'","closed")'),'visible'=>!user()->isAdmin),
);
  
$this->spageindexWidget(array_merge(
    array('breadcrumbs'=>$this->breadcrumbs),
    array('menu'  => $this->menu),
    array('flash' => $this->modelType),
    array('hideHeading' => false),
    array('sidebars' => $this->getProfileSidebar()),
    $config));
