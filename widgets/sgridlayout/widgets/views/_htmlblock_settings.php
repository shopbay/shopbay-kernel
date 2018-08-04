<div class="form-group static-text">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Id');?></label>
    <div class="col-sm-10">
      <p id="widget_id" data-field="widget_id" data-field-type="text" class="form-control-static form-field"></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-1 control-label"><?php echo Sii::t('sii','Content');?></label>
    <div class="col-sm-10">
        <?php   $element->getLanguageForm('html',[
                    'class'=>'form-control language-field-html',
                    'data-field-type'=>'text',
                    'rows'=>5,
                    'placeholder'=>Sii::t('sii','Enter content'),
                ],'textArea','html');
        ?>
    </div>
</div>