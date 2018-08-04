<div class="media-upload-form">
    <?php $this->getMediaUploadForm();?>
    <span class="loading-gif" style="display:none;"><?php echo CHtml::image($this->getImage('loading',false));?></span>
</div>

<div class="form">

    <?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>'media-form',
        'enableAjaxValidation'=>false,
    )); ?>

    <?php //Dummy field so that CreateAction will trigger $_POST save
          echo $form->hiddenField($model,'id');
    ?>

    <div class="row" style="margin-top:100px;">
        <?php
            $this->widget('zii.widgets.jui.CJuiButton',[
                    'name'=>'actionButton',
                    'buttonType'=>'button',
                    'caption'=> Sii::t('sii','Save'),
                    'value'=>'actionbtn',
                    'onclick'=>'js:function(){submitform(\'media-form\');}',
            ]);
        ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->
