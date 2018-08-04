<?php $this->module->registerFormCssFile();?>
<div class="form">
    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'email-form',
            'enableAjaxValidation'=>false,
    )); ?>

    <p class="note"><?php echo Sii::t('sii','Fields with <span class="required">*</span> are required.');?></p>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
            <?php echo $form->labelEx($model,'password'); ?>
            <?php echo $form->passwordField($model,'password',array('size'=>30,'maxlength'=>PasswordForm::POLICY_LENGTH_MAXIMUM,'style'=>'font-size:1.5em')); ?>
            <?php echo $form->error($model,'password'); ?>
    </div>

    <div class="row">
            <?php echo $form->labelEx($model,'email'); ?>
            <?php echo $form->textField($model,'email',array('size'=>48,'maxlength'=>100)); ?>
            <?php echo $form->error($model,'email'); ?>
    </div>

    <div class="row">
            <?php echo $form->labelEx($model,'cemail'); ?>
            <?php echo $form->textField($model,'cemail',array('size'=>48,'maxlength'=>100)); ?>
            <?php echo $form->error($model,'cemail'); ?>
    </div>


    <?php if(CCaptcha::checkRequirements()): ?>
    <div class="row">
        <?php $this->widget('CCaptcha',array(
                'buttonType'=>'button',
                'captchaAction'=> 'management/captcha',
                'clickableImage'=>true,
                'showRefreshButton'=>false,
                'imageOptions'=>array('style'=>'cursor:pointer','title'=>Sii::t('sii','Click to Refresh')),
            )); 
        ?>
        <br/>
        <small>
            <?php echo Sii::t('sii','Please enter the letters (case-insensitive) as they are shown in the image above.');?>
            <br><?php echo Sii::t('sii','If you cannot see the image clearly, click on the image to get a new one.');?>
        </small>
        <br><?php echo CHtml::error($model,'verify_code'); ?>
        <?php echo CHtml::activeTextField($model, 'verify_code'); ?>
        <br/>
    </div>
    <?php endif; ?>


    <div class="row buttons" style="padding-top:20px">
        <?php $this->widget('zii.widgets.jui.CJuiButton',array(
                        'name'=>'emailbutton',
                        'buttonType'=>'button',
                        'caption'=>Sii::t('sii','Confirm'),
                        'value'=>'btn1',
                        'onclick'=>'js:function(){submitform(\'email-form\');}',
                    ));
             ?>

    </div>

    <?php $this->endWidget(); ?>

</div><!-- form -->