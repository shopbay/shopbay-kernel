<?php cs()->registerScript('chosen','$(\'.chzn-select\').chosen();$(\'.chzn-search\').hide();',CClientScript::POS_END);?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>'ticket-form',
        'enableAjaxValidation'=>false,
)); ?>

        <?php if ($model->isNewRecord):?>
        <p class="note"><?php echo Sii::t('sii','Fields with <span class="required">*</span> are required.');?></p>
        <?php endif;?>

        <?php //echo $form->errorSummary($model,null,null,array('style'=>'width:57%')); ?>

        <div class="row">
            <?php echo $form->labelEx($model,'subject'); ?>
            <?php echo $form->textField($model,'subject',array('size'=>80,'maxlength'=>200)); ?>
            <?php //echo $form->error($model,'subject'); ?>
        </div>
        
        <?php if ($this->module->enableShopField):?>
        <div class="row" style="margin-bottom:15px;">
            <?php echo $form->labelEx($model,'shop_id',array('style'=>'margin-bottom:3px;')); ?>
            <?php echo Chtml::activeDropDownList($model,
                            'shop_id',
                            $model->getShopsArray(user()->getLocale()),
                            array('prompt'=>'',
                                   'class'=>'chzn-select',
                                   'data-placeholder'=>Sii::t('sii','Select Shop'),
                                   'style'=>'width:435px;'));
            ?>
            <?php echo $form->error($model,'shop_id'); ?>
        </div>
        <?php endif;?>
        
        <div class="row">
            <?php echo $form->labelEx($model,'content'); ?>
            <?php echo $form->textArea($model,'content',array('rows'=>6, 'style'=>'font-size:1.1em;')); ?>
            <?php //echo $form->error($model,'content'); ?>
        </div>

        <div class="row" style="margin-top:20px;">
            <?php 
                $this->widget('zii.widgets.jui.CJuiButton',
                    array(
                        'name'=>'actionButton',
                        'buttonType'=>'button',
                        'caption'=> Sii::t('sii','Submit'),
                        'value'=>'actionbtn',
                        'onclick'=>'js:function(){submitform(\'ticket-form\');}',
                        )
                );
             ?>
        </div>

<?php $this->endWidget(); ?>

</div><!-- form -->