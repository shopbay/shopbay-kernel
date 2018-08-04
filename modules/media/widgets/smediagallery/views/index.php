<div class="media-gallery-dialog form rounded" data-form-model="<?php echo $formModel;?>">

    <div class="row header">
        <h1><?php echo Sii::t('sii','Media Gallery'); ?></h1>
    </div>
    <?php echo $this->renderGallery(); ?>
    
    <div class="row button">
        <?php $this->widget('zii.widgets.jui.CJuiButton',[
                    'name'=>'mediaGalleryConfirmButton',
                    'buttonType'=>'button',
                    'caption'=>Sii::t('sii','Confirm'),
                    'value'=>'confirmBtn',
                    'htmlOptions'=>['data-route'=>$route,'class'=>'ui-button'],
                ]);
         ?>
        <?php $this->widget('zii.widgets.jui.CJuiButton',[
                    'name'=>'mediaGalleryCancelButton',
                    'buttonType'=>'button',
                    'caption'=>Sii::t('sii','Cancel'),
                    'value'=>'cancelBtn',
                    'htmlOptions'=>['data-container'=>$container,'class'=>'ui-button'],
                ]);
         ?>
    </div>
</div>
