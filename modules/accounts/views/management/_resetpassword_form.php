<?php $this->module->registerFormCssFile();?>
<div class="form-wrapper password-reset-form rounded">
    <div class="form form-container">

        <div class="form-heading"><?php echo Sii::t('sii','Forgot Password');?></div>

        <div id="flash-bar">
            <?php $this->sflashwidget(get_class($model));?>
        </div>    

        <p class="note"><?php echo Sii::t('sii','Please enter the email address you have registered with us.');?></p>

        <?php $form=$this->beginWidget('CActiveForm', array(
                'id'=>'reset-password-form',
                'enableAjaxValidation'=>false,
        )); ?>

        <div class="form-row" style="margin-top:30px">
                <?php echo $form->label($model,'email',array('class'=>'form-label')); ?>
                <?php echo $form->textField($model,'email',array('class'=>'form-input','maxlength'=>100)); ?>
                <?php echo $form->error($model,'email'); ?>
        </div>

        <?php if(CCaptcha::checkRequirements()): ?>
        <div class="form-row">
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

        <div class="form-row">
            <input class="ui-button" id="emailbutton" name="emailbutton" type="submit" value="<?php echo Sii::t('sii','Submit');?>">
        </div>

        <?php $this->endWidget(); ?>

    </div>
</div>