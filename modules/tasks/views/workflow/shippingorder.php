<?php
$this->breadcrumbs=array(
    	sii::t('sii','Tasks')=>url('tasks'),
        Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText($action),'{model}'=>$model->displayName(Helper::PLURAL)))=>$model->getTaskUrl($action),
        $model->shipping_no,
);
$this->menu=array();

$this->widget('common.widgets.spage.SPage',array(
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
            'name'=> ($model->actionable(user()->currentRole,user()->getId())?Process::getActionText($action):'').' '.CHtml::link($model->shipping_no,$model->viewUrl),
            'tag'=> $model->getStatusText(),
            'superscript'=>$model->getShippingName(),
        ),
    'description'=>$model->getWorkflowDescription(),
    'body'=>$this->renderPartial('_workflowform',array('model'=>$model,'action'=>$action,'decision'=>$decision),true),
    'sections'=>$sections,
    'csrfToken' => true, //needed by tasks.js
));
