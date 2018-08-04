<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Width');?></label>
    <div class="col-sm-9">
        <select id="size" name="size" data-field="size" class="form-control form-field form-inline-element">
            <?php 
                for ($i = 1; $i <= 12; $i++) {
                    $selected = $i==$element->size ? 'selected' : '';
                    echo '<option value="'.$i.'" '.$selected.' >'.$i.'</option>';
                } 
            ?>
        </select>
        <span class="static"><?php echo Sii::t('sii','column unit');?></span>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Style');?></label>
    <div class="col-sm-9">
        <textarea id="style" name="style" data-field="style" class="form-control form-field" placeholder="<?php echo Sii::t('sii','Enter your custom css style');?>" rows="3"><?php echo $element->style;?></textarea>
    </div>
</div>
