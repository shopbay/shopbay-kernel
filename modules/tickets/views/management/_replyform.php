<?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>$model->id,
        'enableAjaxValidation'=>false,
    )); 

    echo $form->hiddenField($model,'target'); 
    echo $form->hiddenField($model,'group'); 
    echo $form->hiddenField($model,'id'); 

    echo CHtml::openTag('div',array('class'=>'reply-form-wrapper'));
    
    echo CHtml::activetextArea($model,'content',array('placeholder'=>Sii::t('sii','Enter your reply here..'),'cols'=>50,'style'=>'overflow-y:auto','disabled'=>user()->isGuest?true:false));
    
    $this->widget('zii.widgets.jui.CJuiButton',
                    array(
                        'name'=>'reply-button',
                        'buttonType'=>'button',
                        'caption'=>Sii::t('sii','Submit Reply'),
                        'value'=>'replybtn',
                        'htmlOptions'=>array('style'=>'margin-left:10px'),
                        'options'=>array('disabled'=>isset($preview)?true:false),
                        'htmlOptions'=>array('form'=>$model->id,'class'=>'reply-button'),
                        'onclick'=>'js:function(){'.$model->formScript.'}',
                        )
                ); 

    echo CHtml::error($model,'content',array('style'=>'color:red'));
    
    echo CHtml::closeTag('div');
    
    $this->endWidget();
    