<?php echo CHtml::openTag('div', ['class'=>'sgridtextblock '.$element->name,'style'=>$element->style]);?>
    
    <?php if (strlen($element->getLanguageValue('title'))>0):?>
        <h2><?php echo $element->getLanguageValue('title');?></h2>
    <?php endif;?>
    
    <?php echo $element->renderText();?>
    
<?php echo CHtml::closeTag('div');
