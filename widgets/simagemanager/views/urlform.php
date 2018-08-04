<div class="imageurlform-dialog form rounded" data-form-model="<?php echo $formModel;?>">
    
    <div class="row">
        <?php echo CHtml::label(Sii::t('sii','Please paste your image url here'),''); ?>
        <?php echo CHtml::textField('imageurl','',array('maxlength'=>1000)); ?>
    </div>  
    
    <div class="row note">
        <?php echo Sii::t('sii','If your URL is correct, you will see image preview after you click "Confirm". Large image may take few minutes to appear.'); ?>
    </div>  

    <div class="row" style="margin-top:30px">
        <?php $this->widget('zii.widgets.jui.CJuiButton',
                    array(
                        'name'=>'imageUrlConfirmButton',
                        'buttonType'=>'button',
                        'caption'=>Sii::t('sii','Confirm'),
                        'value'=>'confirmBtn',
                        'htmlOptions'=>array('data-route'=>$route,'class'=>'ui-button'),
                    )
                );
         ?>
        <?php $this->widget('zii.widgets.jui.CJuiButton',
                    array(
                        'name'=>'imageUrlCancelButton',
                        'buttonType'=>'button',
                        'caption'=>Sii::t('sii','Cancel'),
                        'value'=>'cancelBtn',
                        'htmlOptions'=>array('data-container'=>$container,'class'=>'ui-button'),
                    )
                );
         ?>
    </div>
</div>
