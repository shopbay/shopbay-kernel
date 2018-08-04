<div class="form">

    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'message-form',
            'enableAjaxValidation'=>false,
    )); ?>

        <p class="note"><?php echo Sii::t('sii','Fields with <span class="required">*</span> are required.');?></p>
        
	<?php //echo $form->errorSummary($model); ?>
        
        <?php if (SActiveSession::exists(SActiveSession::MESSAGE_COMPOSE)):?>
        <div class="row" style="margin-top:10px">
		<?php echo $form->labelEx($model,'recipient'); ?>
		<?php echo $model->recipientName; ?>
	</div>
        <?php else: ?>
	<div class="row" style="margin-top:10px">
		<?php echo $form->labelEx($model,'recipient'); ?>
		<?php echo $form->textField($model,'recipient',array('size'=>75,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'recipient'); ?>
	</div>
        <?php endif;?>
        
	<div class="row" style="margin-top:10px">
		<?php echo $form->labelEx($model,'subject'); ?>
		<?php echo $form->textField($model,'subject',array('size'=>75,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'subject'); ?>
	</div>

	<div class="row">
            <?php echo $form->labelEx($model,'content',array('style'=>'margin-bottom:5px')); ?>
            <?php echo $form->textArea($model,'content',array('size'=>60,'maxlength'=>1000)); ?>
            <?php echo $form->error($model,'content'); ?>
	</div>

        <div class="row" style="margin-top:20px;">
            <?php 
                $this->widget('zii.widgets.jui.CJuiButton',
                    array(
                        'name'=>'actionButton',
                        'buttonType'=>'button',
                        'caption'=>Sii::t('sii','Send'),
                        'value'=>'actionbtn',
                        'onclick'=>'js:function(){submitform(\'message-form\');}',
                        )
                );
             ?>
	</div>

    <?php $this->endWidget(); ?>

</div><!-- form -->
<?php
Yii::app()->clientScript->registerScript('ckeditor','CKEDITOR.replace(\'Message_content\',{
    customConfig : \''.$this->getModule()->getAssetsURL($this->getModule()->pathAlias.'.js').'/'.$this->getModule()->getAssetFilename('messageckeditor.js').'\',
});');