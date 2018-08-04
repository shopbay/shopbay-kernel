<div class="form-group static-text">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Id');?></label>
    <div class="col-sm-9">
      <p id="widget_id" data-field="widget_id" data-field-type="text" class="form-control-static form-field"></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Heading');?></label>
    <div class="col-sm-9">
        <?php   $element->getLanguageForm('title',[
                    'class'=>'form-control language-field-title',
                    'placeholder'=>Sii::t('sii','Enter heading'),
                ]);
        ?>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Description');?></label>
    <div class="col-sm-9">
        <?php   $element->getLanguageForm('desc',[
                    'class'=>'form-control language-field-desc',
                    'data-field-type'=>'text',
                    'rows'=>3,
                    'placeholder'=>Sii::t('sii','Enter description'),
                ],'textArea');
        ?>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Image');?></label>
    <div class="col-sm-9 image-form-wrapper single">
        <input id="bgImage" name="bgImage" data-field="bgImage" data-field-type="bgImage" type="hidden" class="form-control-static form-field" value="<?php echo $element->bgImage;?>">
        <?php $this->getImageForm($element); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Button');?></label>
    <div class="col-sm-9">
        <?php   $element->getLanguageForm('ctaLabel',[
                    'class'=>'form-control form-inline-element language-field-ctaLabel',
                    'placeholder'=>Sii::t('sii','Enter label'),
                ]);
        ?>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><!-- blank --></label>
    <div class="col-sm-9">
        <input id="ctaUrl" name="ctaUrl" data-field="ctaUrl" data-field-type="link" type="text" class="form-control form-field form-inline-element" placeholder="<?php echo Sii::t('sii','Enter button link, e.g. https://yourshop/yourpage');?>" value="<?php echo $element->ctaUrl;?>">
    </div>
</div>
