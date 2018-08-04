<?php
if (user()->isAuthenticated){
    $this->breadcrumbs=array(
            Sii::t('sii','Tasks')=>url('tasks'),
            Sii::t('sii','{action} Items',array('{action}'=>isset($decision)?Process::getDecisionText($decision):Process::getActionText($action)))=>$model->getTaskUrl($model->getWorkflow()->parseAction()),
            $model->displayLanguageValue('name',user()->getLocale()),
    );
    $this->menu=array();
}

$this->widget('common.widgets.spage.SPage',array(
    'id'=>'item_workflow',
    'breadcrumbs'=>$this->breadcrumbs,
    'menu'=>$this->menu,
    'flash'  => get_class($model),
    'heading'=> array(
        'name'=> ($model->actionable(user()->currentRole,user()->getId())?isset($decision)?Process::getDecisionText($decision):Process::getActionText($action):'')
                .' '.$model->displayLanguageValue('name',user()->getLocale()),
        'tag'=> $model->getStatusText(),
        'superscript'=> user()->currentRole==Role::MERCHANT?
                         CHtml::link($model->order_no,$model->order->viewUrl).', '.CHtml::link($model->shipping_order_no,$model->shippingOrder->viewUrl):
                         CHtml::link($model->order_no,Order::getAccessUrl($model->order_no, $model->byGuestCustomer()))
    ),
    'description'=>$model->getWorkflowDescription(),
    'body'=>$this->renderPartial('_workflowform',array('model'=>$model,'action'=>$action,'decision'=>$decision),true),
    'sections'=>$sections,
    'csrfToken' => true, //needed by tasks.js
));
