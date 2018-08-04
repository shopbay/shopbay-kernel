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
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Item');?></label>
    <div class="col-sm-9">
        <select id="listItem" name="listItem" data-field="listItem"  data-field-type="listItem" class="form-control form-field" placeholder="<?php echo Sii::t('sii','Select List Item');?>" >
            <?php 
                foreach ($element->layout->getListItemList() as $key => $value) {
                    $selected = $key==$element->listItem ? 'selected' : '';
                    echo '<option value="'.$key.'" '.$selected.' >'.$value.'</option>';
                } 
            ?>
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Display');?></label>
    <div class="col-sm-9">
        <input id="viewData" name="viewData" data-field="viewData" type="hidden" class="form-control-static form-field" value="<?php echo $element->serializeValue('viewData');?>">
        <?php 
        /**
         * @todo Open below params to be editable (when support theme file level editing)
         * $beforeViewFile, $afterViewFile, $itemScript
         */
        ?>
        <input id="beforeViewFile" name="beforeViewFile" data-field="beforeViewFile" type="hidden" class="form-control-static form-field" value="<?php echo $element->beforeViewFile;?>">
        <input id="afterViewFile" name="afterViewFile" data-field="afterViewFile" type="hidden" class="form-control-static form-field" value="<?php echo $element->afterViewFile;?>">
        <input id="itemScript" name="itemScript" data-field="itemScript" type="hidden" class="form-control-static form-field" value="<?php echo $element->itemScript;?>">
        <select id="itemsPerRow" name="itemsPerRow" data-field="itemsPerRow" class="form-control  form-field form-inline-element">
            <?php 
                for ($i = 1; $i <= 5; $i++) {
                    $selected = $i==$element->itemsPerRow ? 'selected' : '';
                    echo '<option value="'.$i.'" '.$selected.' >'.$i.'</option>';
                } 
            ?>
        </select>
        <span class="static"><?php echo Sii::t('sii','per row, ');?></span>
        <span class="static"><!-- spacing --></span>
        <select id="itemsLimit" name="itemsLimit" data-field="itemsLimit" class="form-control form-field form-inline-element">
            <?php 
                for ($i = 1; $i <= 12; $i++) {
                    $selected = $i==$element->itemsLimit ? 'selected' : '';
                    echo '<option value="'.$i.'" '.$selected.' >'.$i.'</option>';
                } 
            ?>
        </select>
        <span class="static"><?php echo Sii::t('sii','items per page');?></span>
        <br>
        <br>
        <span class="static"><i><?php echo Sii::t('sii','Note: A page may contain multiple rows');?></i></span>
    </div>
</div>