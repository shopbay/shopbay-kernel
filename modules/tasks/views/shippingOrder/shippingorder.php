<?php 
$this->module->registerScriptFile('common.widgets.spagesection.assets.js','spagesection.js');
$this->module->registerCssFile('common.widgets.spagesection.assets.css','spagesection.css');
$this->module->registerFormCssFile();
$this->module->registerGridViewCssFile();
$this->module->registerChosen();

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
        'superscript'=>null,
        'subscript'=>null,
    ),
    'body'=>$this->renderPartial('_shippingorders',array('dataProvider'=>$dataProvider,'searchModel'=>$searchModel),true),
    'csrfToken' => true,
));