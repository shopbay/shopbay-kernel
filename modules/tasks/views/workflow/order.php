<?php
if (user()->isAuthenticated){
    $this->breadcrumbs = array(
        Sii::t('sii','Tasks')=>url('tasks'),
        Sii::t('sii','{action} {model}',array('{action}'=>Process::getActionText($action),'{model}'=>$model->displayName(Helper::PLURAL)))=>$model->getTaskUrl($action),
        $model->order_no,
    );
    $this->menu=array(
        array('id'=>'contact','title'=>Sii::t('sii','Contact Merchant'),'subscript'=>Sii::t('sii','contact'), 'url'=>$model->contactMerchantUrl),
    );
}

$this->widget('common.widgets.spage.SPage',array(
    'id'=>'order_workflow',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
        'name'=> ($model->actionable(user()->currentRole,user()->getId())?isset($decision)?Process::getDecisionText($decision):Process::getActionText($action):'')
                .' '.CHtml::link($model->order_no,$model->viewUrl),
        'tag'=> $model->getStatusText(),
        'superscript'=>null,
        'subscript'=>null,
    ),
    'description'=>$model->getWorkflowDescription(),
    'body'=>$this->renderPartial('_workflowform',array('model'=>$model,'action'=>$action,'decision'=>$decision),true),
    'sections'=>$sections,
    'csrfToken' => true, //needed by tasks.js
));
