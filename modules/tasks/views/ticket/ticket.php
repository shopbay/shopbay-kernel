<?php $this->getModule()->registerFormCssFile();?>
<?php $this->getModule()->registerGridViewCssFile();?>
<?php
$this->breadcrumbs=array(
    	Sii::t('sii','Tasks')=>url('tasks'),
        Sii::t('sii','{action} Ticket',array('{action}'=>ucfirst($action))),
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => array(get_class($searchModel),'hint'),
    'heading'=> array(
        'name'=> Sii::t('sii','{action} Ticket',array('{action}'=>ucfirst($action))),
        'superscript'=>null,
        'subscript'=>null,
    ),
    'body'=>$this->renderPartial('_tickets',array('dataProvider'=>$dataProvider,'searchModel'=>$searchModel), true),
    'csrfToken' => true,
));