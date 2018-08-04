<div class="form-group static-text">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Id');?></label>
    <div class="col-sm-9">
      <p id="widget_id" data-field="widget_id" data-field-type="text" class="form-control-static form-field"></p>
    </div>
</div>


<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Images');?></label>
    <div class="col-sm-9 image-form-wrapper multiple" data-next-num="<?php echo count($element->items);?>">
        <?php $this->getImageForm($element,true); ?>
    </div>
</div>