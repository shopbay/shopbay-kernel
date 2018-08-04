<div class="form-group static-text">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Id');?></label>
    <div class="col-sm-10">
      <p id="widget_id" data-field="widget_id" data-field-type="text" class="form-control-static form-field"></p>
    </div>
</div>

<div class="form-group static-text">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Fixture');?></label>
    <div class="col-sm-10">
        <input id="fixtures" name="fixtures" data-field="fixtures" type="hidden" class="form-control-static form-field" value="<?php echo $element->serializeValue('fixtures');?>">
        <ul class="form-control-static">
            <?php foreach ($element->fixtures as $fixture): ?>
            <li>
                <p>
                    <?php echo $fixture;?>
                </p>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>