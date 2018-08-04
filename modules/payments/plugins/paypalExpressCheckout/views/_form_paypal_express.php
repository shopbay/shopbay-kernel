<?php cs()->registerScript('chosen-apimode','$(\'.chzn-select-apimode\').chosen();$(\'.chzn-search\').hide();',CClientScript::POS_END);?>
<div class="subform">
    
    <div class="row">
        <?php $model->renderForm($this); ?>
    </div>    
    
    <div class="row">
        <?php echo CHtml::activeLabelEx($model,'apiMode',array('style'=>'margin-bottom:3px;')); ?>
        <?php echo CHtml::activeDropDownList($model,'apiMode', 
                                        $model::getModes(), 
                                        array('prompt'=>'',
                                              'class'=>'chzn-select-apimode',
                                              'data-placeholder'=>Sii::t('sii','Select Mode'),
                                              'style'=>'width:330px;'));
        ?>
        <?php echo CHtml::error($model,'apiMode'); ?>
    </div>
    
    <div class="row" style="margin-top: 15px;">
        <?php echo CHtml::activeLabelEx($model,'email'); ?>
        <?php echo CHtml::activeTextField($model,'email',array('size'=>60,'maxlength'=>100)); ?>
        <?php $this->stooltipWidget($model->getTooltip('email')); ?>
        <?php echo CHtml::error($model,'email'); ?>
    </div>
    
    <div class="row" style="margin-top:10px;">
        <?php echo CHtml::activeLabelEx($model,'apiUsername'); ?>
        <?php echo CHtml::activeTextField($model,'apiUsername',array('size'=>60,'maxlength'=>100)); ?>
        <?php echo CHtml::error($model,'apiUsername'); ?>
    </div>
    
    <div class="row">
        <?php echo CHtml::activeLabelEx($model,'apiPassword'); ?>
        <?php echo CHtml::activeTextField($model,'apiPassword',array('size'=>60,'maxlength'=>100)); ?>
        <?php echo CHtml::error($model,'apiPassword'); ?>
    </div>
    
    <div class="row">
        <?php echo CHtml::activeLabelEx($model,'apiSignature'); ?>
        <?php echo CHtml::activeTextField($model,'apiSignature',array('size'=>60,'maxlength'=>200)); ?>
        <?php echo CHtml::error($model,'apiSignature'); ?>
    </div>
    
</div>

<?php $this->renderPartial('_form_button',array('model'=>$model)); ?>