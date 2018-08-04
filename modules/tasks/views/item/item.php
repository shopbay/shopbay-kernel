<?php $this->module->registerScriptFile('common.widgets.spagesection.assets.js','spagesection.js');?>
<?php $this->module->registerCssFile('common.widgets.spagesection.assets.css','spagesection.css');?>
<?php $this->module->registerFormCssFile();?>
<?php $this->module->registerGridViewCssFile();?>
<?php $this->module->registerChosen();?>
<?php
$this->breadcrumbs=array(
    	Sii::t('sii','Tasks')=>url('tasks'),
        Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($action)),'{model}'=>$searchModel->displayName())),    
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => array(get_class($searchModel),'hint'),
    'heading'=> array(
        'name'=> Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText(ucfirst($action)),'{model}'=>$searchModel->displayName(Helper::PLURAL))),
    ),
    'body'=>$this->renderPartial($this->module->getView('tasks.itemtasks'),$this->getViewData($dataProvider,$searchModel),true),
    'csrfToken' => true,
));