<?php $this->getModule()->registerGridViewCssFile();?>
<?php $this->getModule()->registerFormCssFile();?>
<?php
$this->breadcrumbs=array(
    	Sii::t('sii','Tasks')=>url('tasks'),
        Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($action)),'{model}'=>$searchModel->displayName())),    
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => $this->id,
    'heading'=> array(
        'name'=> Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($action)),'{model}'=>$searchModel->displayName(Helper::PLURAL))),
    ),
    'body'=>$this->renderPartial('_questions',array('dataProvider'=>$dataProvider,'searchModel'=>$searchModel,'checkboxInvisible'=>true),true),
    'csrfToken' => true,
));