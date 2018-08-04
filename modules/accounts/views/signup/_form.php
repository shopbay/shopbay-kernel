<div class="form-wrapper rounded">
    
    <div class="form form-container">

        <div class="form-heading"><?php echo Sii::t('sii','Sign up');?></div>

        <div id="flash-bar">
            <?php $this->sflashwidget(get_class($model));?>
        </div>    

        <?php $form=$this->beginWidget('CActiveForm', array(
                'id'=>'signup-form',
                'action'=>url('account/signup'),
                'enableAjaxValidation'=>true,
        )); ?>

        <?php //echo $form->errorSummary($model); ?>

        <!--<div class="form-row">
            <?php //echo $form->label($model,'name',array('class'=>'form-label')); ?>
            <?php //echo $form->textField($model,'name',array('class'=>'form-input','maxlength'=>32,'autofocus'=>'autofocus','placeholder'=>$model->getAttributeLabel('name'))); ?>
            <?php //echo $form->error($model,'name'); ?>
        </div>-->

        <div class="form-row">
            <?php //echo $form->label($model,'email',array('class'=>'form-label')); ?>
            <?php echo $form->textField($model,'email',array('class'=>'form-input','maxlength'=>100,'placeholder'=>$model->getAttributeLabel('email'))); ?>
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
                                'captchaAction'=> 'captcha',
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
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <div class="form-row">
            <?php //echo $form->checkBox($model,'accept',array('style'=>'margin-left:10px;')); ?>
            <!--<span class="form-link">
                <?php //echo l($model->getAttributeLabel('accept'),url('terms'));?>
            </span>-->
            <?php 
                $this->widget('zii.widgets.jui.CJuiButton',array(
                    'name'=>'signup-button',
                    'buttonType'=>'submit',
                    'caption'=>Sii::t('sii','Create Account'),
                    'value'=>'btn',
                    'htmlOptions'=>array('class'=>'ui-button','style'=>'background:lightgreen;margin-top:10px;'),
                )); 
            ?>
        </div>

        <div class="form-row tos">
            <?php echo $model->getAttributeLabel('acceptTOS'); ?>
        </div>

        <?php $this->endWidget(); ?>

    </div>
    <div class="link-container">
        <span class="form-link">
            <?php if (isset($nonAjax))
                      echo Sii::t('sii','Already have an account? {signin}',array('{signin}'=>CHtml::link(Sii::t('sii','Log in'),url('signin'))));
                  else
                      echo Sii::t('sii','Already have an account? {signin}',array('{signin}'=>CHtml::link(Sii::t('sii','Log in'),'javascript:void(0);',array('onclick'=>'signin("'.$this->authHostInfo.'");'))));
            ?>            
        </span>
    </div>
</div>