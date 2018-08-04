<?php cs()->registerScript('chosen','$(\'.chzn-select\').chosen();$(\'#AccountProfile_gender_chzn .chzn-search\').hide();$(\'#AccountProfile_locale_chzn .chzn-search\').hide();',CClientScript::POS_END);?>
<?php //cs()->registerScript('chosen2','$(\'.chzn-select-country\').chosen();$(\'#AccountAddress_country_chzn .chzn-search\').hide();',CClientScript::POS_END);?>
<?php
/* @var $this ProfileController */
/* @var $model Account */
/* @var $form CActiveForm */
?>
<div class="form" >
    
    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'account-profile-form',
            'action'=>url('account/management/update'),
        )); 
    ?>

    <?php //echo $form->errorSummary($model->profile); ?>
    <?php //if (isset($model->address))
          //    echo $form->errorSummary($model->address); 
    ?>

    <div class="row">
        <div class="column">
            <?php echo $form->labelEx($model->profile,'first_name'); ?>
            <?php echo $form->textField($model->profile,'first_name',array('size'=>35,'maxlength'=>50,'placeholder'=>$model->getAttributeLabel('first_name'))); ?>
            <?php echo $form->error($model->profile,'first_name'); ?>
        </div>
        <div class="column">
            <?php echo $form->labelEx($model->profile,'last_name'); ?>
            <?php echo $form->textField($model->profile,'last_name',array('size'=>35,'maxlength'=>50,'placeholder'=>$model->getAttributeLabel('last_name'))); ?>
            <?php echo $form->error($model->profile,'last_name'); ?>
        </div>
    </div>
    
    <div class="row" style="padding-top: 10px;clear:left;">
        <?php echo $form->labelEx($model->profile,'alias_name'); ?>
        <?php echo $form->textField($model->profile,'alias_name',array('size'=>60,'maxlength'=>50)); ?>
        <?php $this->stooltipWidget($model->profile->getToolTip('alias_name')); ?>
        <?php echo $form->error($model->profile,'alias_name'); ?>
    </div>

    <div class="row">
        <div class="column">
            <?php echo $form->labelEx($model->profile,'mobile'); ?>
            <?php echo $form->textField($model->profile,'mobile',array('size'=>20,'maxlength'=>20)); ?>
            <?php echo $form->error($model->profile,'mobile'); ?>
        </div>
        <div class="column">
            <?php echo $form->labelEx($model->profile,'birthday'); ?>
            <?php 
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                    'name'=>'AccountProfile[birthday]',
                    'value'=>$model->profile->birthday,
                    // additional javascript options for the date picker plugin
                    'options'=>array(
                        'showAnim'=>'fold',
                        'showOn'=>'both',
                        'changeMonth'=>true,
                        'changeYear'=>true,
                        'yearRange'=>'-100:+0',
                        'dateFormat'=> 'yy-mm-dd',
                        'gotoCurrent'=>false,
                        'buttonImage'=> $this->getImage('datepicker',false),
                        'buttonImageOnly'=> true,
                    ),
                    'htmlOptions'=>array(
                        'style'=>'margin-right:5px;vertical-align:middle;width:175px;',

                    ),
                ));
            ?>    
            <?php echo $form->error($model->profile,'birthday'); ?> 
        </div>
    </div>

    <div class="row" style="padding-top: 15px;clear:left;">
        <div class="column">
            <?php echo $form->labelEx($model->profile,'gender',array('style'=>'margin-bottom:3px;')); ?>
            <?php echo $form->dropDownList($model->profile,'gender', 
                                            array('M' => Sii::t('sii','Male'),
                                                  'F' => Sii::t('sii','Female')),
                                            array('class'=>'chzn-select',
                                                  'prompt'=>'',
                                                  'data-placeholder'=>Sii::t('sii','Select Gender'),
                                                  'style'=>'width:155px;')); 
            ?>
            <?php echo $form->error($model->profile,'gender'); ?>
        </div>
        <div class="column">
            <?php echo $form->labelEx($model->profile,'locale',array('style'=>'margin-bottom:3px;')); ?>
            <?php echo $form->dropDownList($model->profile,'locale', 
                                            SLocale::getLanguages(),
                                            array('class'=>'chzn-select',
                                                      'prompt'=>'',
                                                      'data-placeholder'=>Sii::t('sii','Select Langague'),
                                                      'style'=>'width:210px;')); 
            ?>
            <?php echo $form->error($model->profile,'locale'); ?>
        </div>
    </div>

    <div class="row" style="padding-top: 15px;clear:left;">
            <?php echo $form->label($model->address,'address1'); ?>
            <?php echo $form->textField($model->address,'address1',array('size'=>60,'maxlength'=>100)); ?>
    </div>
    <div class="row">
            <?php echo $form->textField($model->address,'address2',array('size'=>60,'maxlength'=>100)); ?>
            <?php echo $form->error($model->address,'address1'); ?>
            <?php echo $form->error($model->address,'address2'); ?>
    </div>

    <div class="row">
        <div class="column">
            <?php echo $form->label($model->address,'postcode'); ?>
            <?php echo $form->textField($model->address,'postcode',array('size'=>25,'maxlength'=>20)); ?>
            <?php echo $form->error($model->address,'postcode'); ?>
        </div>
        <div class="column">
            <?php echo $form->label($model->address,'city'); ?>
            <?php echo $form->textField($model->address,'city',array('size'=>30,'maxlength'=>40)); ?>                
            <?php echo $form->error($model->address,'city'); ?>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row">
        <div class="column">
            <?php echo $form->label($model->address,'country',array('style'=>'margin-bottom:3px;')); ?>
            <?php echo $form->dropDownList($model->address,'country',
                                            SLocale::getCountries(),
                                            array('class'=>'chzn-select-country',
                                                  'prompt'=>'',
                                                  'data-placeholder'=>Sii::t('sii','Select Country'),
                                                  'style'=>'width:170px;')); 
            ?>
            <?php echo $form->error($model->address,'country'); ?>
        </div>
        <div class="column">
            <?php echo $form->label($model->address,'state',array('style'=>'margin-bottom:3px;')); ?>
            <?php echo $form->dropDownList($model->address,'state',
                                            SLocale::getStates($model->address->country),
                                            array('class'=>'chzn-select-state',
                                                  'prompt'=>'',
                                                  'data-placeholder'=>Sii::t('sii','Select State'),
                                                  'style'=>'width:200px;')); 
            ?>
            <?php echo $form->error($model->address,'state'); ?>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row" style="margin-top:20px;">
        <?php 
            $this->widget('zii.widgets.jui.CJuiButton',array(
                    'name'=>'profileButton',
                    'buttonType'=>'button',
                    'caption'=>Sii::t('sii','Save'),
                    'value'=>'profilebtn',
                    'onclick'=>'js:function(){submitform(\'account-profile-form\');}',
                ));
         ?>
    </div> 

    <?php $this->endWidget(); ?>
    
</div><!-- form div -->
<?php $this->widget('SStateDropdown',array(
    'stateGetActionUrl'=>url('accounts/management/stateget'),
    'countryFieldId'=>'AccountAddress_country',
    'stateFieldId'=>'AccountAddress_state',
));