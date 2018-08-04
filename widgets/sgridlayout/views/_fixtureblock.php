<?php echo CHtml::openTag('div', ['class'=>'sgridfixtureblock '.$element->name,'style'=>$element->style]);?>
    
    <?php echo $element->renderFixtures(); ?>
    
<?php echo CHtml::closeTag('div');
