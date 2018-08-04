<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>'customer_form',
        'enableAjaxValidation'=>false,
)); ?>

    <?php if ($model->isNewRecord):?>
    <p class="note"><?php echo Sii::t('sii','Fields with <span class="required">*</span> are required.');?></p>
    <?php endif;?>

    <?php echo $form->errorSummary($model,null,null,array('style'=>'width:57%')); ?>

    <?php if ($model->isNewRecord):?>
    <div class="row">
        <?php $model->setScenario('create');//to get label 'required' shown ?>
        <?php echo $form->labelEx($model,'alias_name'); ?>
        <?php echo $form->textField($model,'alias_name',array('size'=>70,'maxlength'=>50)); ?>
        <?php echo $form->error($model,'alias_name'); ?>
    </div>
    <?php endif;?>

    <?php if ($model->profileUpdatable()):?>
        <div class="row">
            <div class="column">
                <?php echo $form->label($model,'first_name'); ?>
                <?php echo $form->textField($model,'first_name',array('size'=>30,'maxlength'=>50)); ?>
                <?php echo $form->error($model,'first_name'); ?>
            </div>
            <div class="column">
                <?php echo $form->label($model,'last_name'); ?>
                <?php echo $form->textField($model,'last_name',array('size'=>30,'maxlength'=>50)); ?>
                <?php echo $form->error($model,'last_name'); ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row">
            <div class="column">
                <?php echo $form->labelEx($model,'gender',array('style'=>'margin-bottom:3px;')); ?>
                <?php echo $form->dropDownList($model,'gender', 
                                                array('M' => Sii::t('sii','Male'),
                                                      'F' => Sii::t('sii','Female')),
                                                array('class'=>'chzn-select',
                                                      'prompt'=>'',
                                                      'data-placeholder'=>Sii::t('sii','Select Gender'),
                                                      'style'=>'width:155px;')); 
                ?>
                <?php echo $form->error($model,'gender'); ?>
            </div>
            <div class="column">
                <?php echo $form->labelEx($model,'birthday'); ?>
                <?php 
                $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'name'=>'Customer[birthday]',
                        'value'=>$model->birthday,
                        // additional javascript options for the date picker plugin
                        'options'=>array(
                            'showAnim'=>'fold',
                            'showOn'=>'both',
                            'changeMonth'=>true,
                            'changeYear'=>true,
                            'yearRange'=>'-100:+0',
                            'dateFormat'=> 'yy-mm-dd',
                            'gotoCurrent'=>true,
                            'buttonImage'=> $this->getImage('datepicker',false),
                            'buttonImageOnly'=> true,
                        ),
                        'htmlOptions'=>array(
                            'style'=>'margin-right:5px;vertical-align:middle;width:175px;',

                        ),
                    ));
                ?>    
                <?php echo $form->error($model,'birthday'); ?> 
            </div>        
            <div class="clearfix"></div>
        </div>
    <?php endif;?>
    
    <?php if ($model->addressUpdatable()):?>
        <div class="row">
            <?php echo $form->label($addressForm,'address1'); ?>
            <?php echo $form->textField($addressForm,'address1',array('size'=>70,'maxlength'=>100)); ?>
            <?php echo $form->error($addressForm,'address1'); ?>
        </div>

        <div class="row">
            <?php echo $form->label($addressForm,'address2'); ?>
            <?php echo $form->textField($addressForm,'address2',array('size'=>70,'maxlength'=>100)); ?>
            <?php echo $form->error($addressForm,'address2'); ?>
        </div>

        <div class="row">
            <div class="column">
                <?php echo $form->label($addressForm,'postcode'); ?>
                <?php echo $form->textField($addressForm,'postcode',array('size'=>30,'maxlength'=>20)); ?>
            </div>
            <div class="column">
                <?php echo $form->label($addressForm,'city'); ?>
                <?php echo $form->textField($addressForm,'city',array('size'=>30,'maxlength'=>40)); ?>

            </div>
            <div class="clear">
                <?php echo $form->error($addressForm,'postcode'); ?>
                <?php echo $form->error($addressForm,'city'); ?>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="row">
            <div class="column">
                <?php echo $form->label($addressForm,'state'); ?>
                <?php echo $form->textField($addressForm,'state',array('size'=>30,'maxlength'=>40)); ?>
            </div>
            <div class="column">
                <?php echo $form->label($addressForm,'country',array('style'=>'margin-bottom:3px;')); ?>
                <?php echo $form->dropDownList($addressForm,'country',
                                                SLocale::getCountries(),
                                                array('class'=>'chzn-select-country',
                                                      'prompt'=>'',
                                                      'data-placeholder'=>Sii::t('sii','Select Country'),
                                                      'style'=>'width:190px;')); 
                ?>
            </div>
            <div class="clear">
                <?php echo $form->error($addressForm,'state'); ?>
                <?php echo $form->error($addressForm,'country'); ?>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="row">
            <?php echo $form->label($addressForm,'mobile'); ?>
            <?php echo $form->textField($addressForm,'mobile',array('size'=>70,'maxlength'=>20)); ?>
            <?php echo $form->error($addressForm,'mobile'); ?>
        </div>
    <?php endif;?>
    
    <div class="row">
        <div class="label-wrapper">
            <?php echo $form->labelEx($model,'tags'); ?>
            <?php $this->stooltipWidget($model->getToolTip('tags')); ?>
        </div>
        <?php echo $form->textField($model,'tags',array('size'=>70,'maxlength'=>500)); ?>
        <?php echo $form->error($model,'tags'); ?>
    </div>

    <div class="row">
        <div class="label-wrapper">
            <?php echo $form->labelEx($model,'notes'); ?>
            <?php $this->stooltipWidget($model->getToolTip('notes')); ?>
        </div>
        <?php echo $form->textArea($model,'notes',array('size'=>70,'rows'=>5)); ?>
        <?php echo $form->error($model,'notes'); ?>
    </div>

    <div class="row" style="margin-top:20px;">
        <?php 
            $this->widget('zii.widgets.jui.CJuiButton',
                array(
                    'name'=>'actionButton',
                    'buttonType'=>'button',
                    'caption'=>$model->isNewRecord ? Sii::t('sii','Create') : Sii::t('sii','Save'),
                    'value'=>'actionbtn',
                    'onclick'=>'js:function(){submitform(\'customer_form\');}',
                    )
            );
         ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->
