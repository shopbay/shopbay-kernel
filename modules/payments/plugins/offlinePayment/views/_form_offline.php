<?php cs()->registerScript('chosen-mode','$(\'.chzn-select-mode\').chosen();$(\'.chzn-search\').hide();',CClientScript::POS_END);?>
<div class="subform">
    <div class="row">
        <?php echo CHtml::activeLabelEx($model,'mode',array('style'=>'margin-bottom:3px;')); ?>
        <?php if ($model->isNewRecord):?>
            <?php echo CHtml::activeDropDownList($model,'mode', 
                                        $model->getAvailableModes(), 
                                        array('prompt'=>'',
                                              'class'=>'chzn-select-mode',
                                              'data-placeholder'=>Sii::t('sii','Select Mode'),
                                              'style'=>'width:250px;'));
            ?>
            <?php echo CHtml::error($model,'mode'); ?>
        <?php else:?>
            <?php echo PaymentMethod::getOfflineName(trim($model->mode,'"'));?>
            <?php echo CHtml::activeHiddenField($model,'mode'); ?>
        <?php endif;?>
    </div>
    
    <div class="row" style="margin-top:20px;">
        <?php if (!$model->isNewRecord):?>
            <?php echo CHtml::activeHiddenField($model,'sourceMethod'); ?>
        <?php endif;?>
        <?php $model->renderForm($this); ?>
    </div>
    
</div>

<?php $this->renderPartial('_form_button',array('model'=>$model)); ?>

<script>
$(document).ready(function(){
    $('#<?php echo get_class($model);?>_mode').change(function() {
      if ($('#<?php echo get_class($model);?>_mode').val().length > 0){
            gettemplate($('#<?php echo get_class($model);?>_mode').val());
      }
    });    
});
</script>

