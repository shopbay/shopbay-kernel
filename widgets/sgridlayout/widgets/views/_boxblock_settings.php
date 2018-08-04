<div class="form-group static-text">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Id');?></label>
    <div class="col-sm-9">
      <p id="widget_id" data-field="widget_id" data-field-type="text" class="form-control-static form-field"></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Image');?></label>
    <div class="col-sm-9 image-form-wrapper single">
        <input id="boxImage" name="boxImage" data-field="boxImage" data-field-type="boxImage" type="hidden" class="form-control-static form-field" value="<?php echo $element->boxImage;?>">
        <?php $this->getImageForm($element); ?>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Caption');?></label>
    <div class="col-sm-9">
        <?php   $element->getLanguageForm('caption',[
                    'class'=>'form-control language-field-caption',
                    'placeholder'=>Sii::t('sii','Enter caption'),
                ]);
        ?>
    </div>
</div>


<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Link');?></label>
    <div class="col-sm-9">
        <input id="link" name="link" data-field="link" data-field-type="link" type="text" class="form-control form-field form-inline-element" placeholder="<?php echo Sii::t('sii','Enter link, e.g. https://yourshop/yourpage');?>" value="<?php echo $element->link;?>">
    </div>
</div>
