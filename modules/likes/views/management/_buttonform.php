<?php 
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'like-'.strtolower($model->type).'-form',
    //'action'=>url('likes/management/create'),
    'enableAjaxValidation'=>false,
)); 

echo $form->hiddenField($model,'type'); 
echo $form->hiddenField($model,'target'); 
echo $form->hiddenField($model,'action'); 
echo $form->hiddenField($model,'modal'); 
echo $form->hiddenField($model,'formObject'); 

if (isset($modal))
    echo l(Sii::t('sii','I like this {item}',array('{item}'=>$model->getObjectDisplayName())),'javascript:void(0);',array('onclick'=>$model->formScript));

echo CHtml::openTag('span',array('class'=>'like-'.strtolower($model->type).'-'.($model->modal?'modal-':'').'button-'.$model->target));
$this->renderPartial($this->module->getView('likes.button'),array('model'=>$model));
echo CHtml::closeTag('span');

$this->endWidget();