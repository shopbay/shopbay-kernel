<?php echo CHtml::openTag('div', ['class'=>'sgridtextblock '.$element->name,'style'=>$element->style]);?>

    <?php if (strlen($element->getLanguageValue('title'))>0):?>
        <h2 id="title" class="form-field" data-field="title" data-field-type="text" ><?php echo $element->getLanguageValue('title');?></h2>
    <?php endif;?>
        
    <?php foreach($element->text as $text):?>
        <p class="text-wrapper"><?php echo $element->getLanguageValue($text,true);?></p>
    <?php endforeach;?>
        
<?php echo CHtml::closeTag('div');?>

