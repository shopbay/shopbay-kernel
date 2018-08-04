<?php
$this->breadcrumbs=array(
    	Sii::t('sii','Tasks')=>url('tasks'),
        Sii::t('sii','{action} {object}',array('{action}'=>Process::getActionText($action),'{object}'=>$model->displayName()))=>$model->getTaskUrl($action),
	$this->parseModelName($model),
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
        'name'=> ($model->actionable(user()->currentRole,user()->getId())?Process::getActionText($action):'').' "'.$this->parseModelName($model).'"',
        'tag'=> $model->getStatusText(),
        'image'=> '<i class="fa fa-gear" title="'.$model->displayName().'"></i>',
    ),
    'body'=>$this->renderPartial('_workflowform',array('model'=>$model,'action'=>$action,'decision'=>$decision),true),
    'sections'=>$sections,
));