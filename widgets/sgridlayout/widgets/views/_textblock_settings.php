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
    <label class="col-sm-1 control-label">
        <?php echo Sii::t('sii','Text');?>
    </label>
    <div class="col-sm-9 text-paragraphs">
        <input id="text" name="text" data-field="text" data-field-type="paragraph" type="hidden" class="form-control-static form-field paragraph-field" value="<?php echo $element->serializeValue('text');?>">
        <?php echo $element->renderTextParagraph(); ?>
    </div>
</div>