<div class="form-group static-text">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Id');?></label>
    <div class="col-sm-9">
      <p id="widget_id" data-field="widget_id" data-field-type="text" class="form-control-static form-field"></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Title');?></label>
    <div class="col-sm-9">
        <?php   $element->getLanguageForm('title',[
                    'class'=>'form-control language-field-title',
                    'placeholder'=>Sii::t('sii','Enter title'),
                ]);
        ?>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Items');?></label>
    <div class="col-sm-9 menu-selections">
        <input id="menu" name="menu" data-field="menu" data-field-type="menu" type="hidden" class="form-control-static form-field menu-field" value="<?php echo $element->serializeValue('menu');?>">
        <?php echo $element->renderMenuSelections(); ?>
    </div>
</div>



