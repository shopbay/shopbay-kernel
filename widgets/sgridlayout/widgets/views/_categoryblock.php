<?php echo CHtml::openTag('div', ['class'=>'sgridcategoryblock '.$element->name,'style'=>$element->style]);?>

    <?php if (strlen($element->getLanguageValue('title'))>0):?>
        <h2 id="title" class="form-field" data-field="title" data-field-type="text" ><?php echo $element->getLanguageValue('title');?></h2>
    <?php endif;?>
        
    <?php echo $element->renderListView(); ?>
        
<?php echo CHtml::closeTag('div');?>

