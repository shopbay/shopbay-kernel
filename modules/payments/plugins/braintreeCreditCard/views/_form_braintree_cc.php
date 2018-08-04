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
    
    <div class="row" style='margin-top: 15px'>
        <?php echo CHtml::activeLabelEx($model,'publicKey'); ?>
        <?php echo CHtml::activeTextField($model,'publicKey',array('size'=>60,'maxlength'=>100)); ?>
        <?php $this->stooltipWidget($model->getTooltip('publicKey')); ?>
        <?php echo CHtml::error($model,'publicKey'); ?>
    </div>
    
    <div class="row">
        <?php echo CHtml::activeLabelEx($model,'privateKey'); ?>
        <?php echo CHtml::activeTextField($model,'privateKey',array('size'=>60,'maxlength'=>100)); ?>
        <?php $this->stooltipWidget($model->getTooltip('privateKey')); ?>
        <?php echo CHtml::error($model,'privateKey'); ?>
    </div>
    
    <div class="row" style="margin-top:10px;">
        <?php echo CHtml::activeLabelEx($model,'merchantId'); ?>
        <?php echo CHtml::activeTextField($model,'merchantId',array('size'=>60,'maxlength'=>100)); ?>
        <?php $this->stooltipWidget($model->getTooltip('merchantId')); ?>
        <?php echo CHtml::error($model,'merchantId'); ?>
    </div>
    
    <div class="row" style="margin-top:10px;">
        <?php echo CHtml::activeLabelEx($model,'merchantAccountId'); ?>
        <?php echo CHtml::activeTextField($model,'merchantAccountId',array('size'=>60,'maxlength'=>100)); ?>
        <?php $this->stooltipWidget($model->getTooltip('merchantAccountId')); ?>
        <?php echo CHtml::error($model,'merchantAccountId'); ?>
    </div>
    
</div>

<?php $this->renderPartial('application.modules.payments.views.management._form_button',array('model'=>$model)); ?>