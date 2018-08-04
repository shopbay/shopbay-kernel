<?php echo CHtml::openTag('div', ['class'=>'sgridhtmlblock '.$element->name,'style'=>$element->style]);?>
    
    <?php echo $element->getLanguageValue('html');?>
    
<?php echo CHtml::closeTag('div');
