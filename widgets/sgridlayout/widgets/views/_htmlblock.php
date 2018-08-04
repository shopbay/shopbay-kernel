<?php echo CHtml::openTag('div', ['class'=>'sgridhtmlblock '.$element->name,'style'=>$element->style]);?>

    <div id="html" data-field="html" data-field-type="html" class="content-wrapper form-field" >
        <?php echo $element->getLanguageValue('html');?>
    </div>
        
<?php echo CHtml::closeTag('div');?>

