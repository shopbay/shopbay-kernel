<?php $form=$this->beginWidget('CActiveForm', [
        'id'=>$model->id,
        //'action'=>url('comments/management/create'),
        'enableAjaxValidation'=>false,
    ]); 

    echo $form->hiddenField($model,'type'); 
    echo $form->hiddenField($model,'target'); 
    echo $form->hiddenField($model,'id'); 
    
    echo CHtml::openTag('div',['class'=>'comment-form-wrapper']);
    
    echo CHtml::activetextArea($model,'content',['placeholder'=>isset($placeholder)?$placeholder:Sii::t('sii','Write a comment for this {target}',array('{target}'=>$model->getTargetDisplayName())),'cols'=>50,'style'=>'overflow-y:auto','disabled'=>user()->isGuest?true:false]);
    
    $this->widget('zii.widgets.jui.CJuiButton',[
                'name'=>'comment-button',
                'buttonType'=>'button',
                'caption'=>isset($buttonText)?$buttonText:Sii::t('sii','Post'),
                'value'=>'commentbtn',
                'options'=>['disabled'=>isset($preview)?true:false],
                'htmlOptions'=>[
                    'form'=>$model->id,
                    'class'=>'comment-button ui-button',
                    'data-script'=>$model->formScript,
                ],
                'onclick'=>'js:function(){'.$model->formScript.'}',
            ]); 

    echo CHtml::error($model,'content',['style'=>'color:red']);
    
    echo CHtml::closeTag('div');
    
    $this->endWidget();
    