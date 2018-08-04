<div class="presignup-container">
    
    <div class="presignup-message">
        <div id="flash-bar">
            <?php $this->sflashwidget(array('presignup','presignup-create-account'));?>
        </div>    
    </div>
    
    <div class="form-wrapper rounded">
        <div class="form form-container">

            <?php $form=$this->beginWidget('CActiveForm', array(
                    'id'=>'signup-form',
                    'action'=>url('account/activate/presignup'),
                    'enableAjaxValidation'=>false,
            )); ?>

            <div id="flash-bar">
                <?php $this->sflashwidget(array(get_class($model)));?>
            </div>    

            <?php //echo $form->errorSummary($model); ?>
            
            <?php echo $form->hiddenField($model,'email'); ?>
            <?php echo $form->hiddenField($model,'network'); ?>
            <?php echo $form->hiddenField($model,'token'); ?>

            <div class="form-row">
                <?php //echo $form->label($model,'email',array('class'=>'form-label')); ?>
                <?php echo $form->textField($model,'email',array('class'=>'form-input disabled','maxlength'=>100,'placeholder'=>$model->getAttributeLabel('email'),'disabled'=>true)); ?>
                <?php $this->stooltipWidget(Sii::t('sii','Account login ID follows the email in your network account')); ?>
                <?php echo $form->error($model,'email'); ?>
            </div>

            <div class="form-row">
                <?php //echo $form->label($model,'password',array('class'=>'form-label')); ?>
                <?php echo $form->passwordField($model,'password',array('class'=>'form-input','maxlength'=>32,'placeholder'=>$model->getAttributeLabel('password'))); ?>
                <?php echo $form->error($model,'password'); ?>
            </div>

            <div class="form-row">
                <?php //echo $form->labelEx($model,'confirmPassword',array('class'=>'form-label')); ?>
                <?php echo $form->passwordField($model,'confirmPassword',array('class'=>'form-input','maxlength'=>32,'placeholder'=>$model->getAttributeLabel('confirmPassword'))); ?>
                <?php echo $form->error($model,'confirmPassword'); ?>
            </div>

            <?php if(CCaptcha::checkRequirements()): ?>
            <div class="form-row">
                <table>
                    <tr>
                        <td>
                            <?php $this->widget('CCaptcha',array(
                                    'id'=>'signup-captcha',
                                    'buttonType'=>'button',
                                    'captchaAction'=>'/accounts/signup/captcha',
                                    'clickableImage'=>true,
                                    'showRefreshButton'=>false,
                                    'imageOptions'=>array('style'=>'cursor:pointer','title'=>Sii::t('sii','Click to Refresh')),
                                )); 
                            ?>
                        </td>
                        <td>
                            <div style="display:inline">
                                <small>
                                    <?php echo Sii::t('sii','Please enter the letters (case-insensitive) at shown at left. Click on the image to get a new one.');?>
                                </small>
                                <br>
                                <?php echo CHtml::activeTextField($model, 'verify_code'); ?>
                                <?php echo $form->error($model,'verify_code'); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php endif; ?>

            <div class="form-row">
                <?php 
                    $this->widget('zii.widgets.jui.CJuiButton',array(
                        'name'=>'signup-button',
                        'buttonType'=>'submit',
                        'caption'=>Sii::t('sii','Register Account'),
                        'value'=>'btn',
                        'htmlOptions'=>array('style'=>'background:lightgreen;margin-top:10px;'),
                    )); 
                ?>
            </div>

            <div class="form-row tos">
                <?php echo $model->getAttributeLabel('acceptTOS'); ?>
            </div>

            <?php $this->endWidget(); ?>

        </div>

    </div>

</div>
