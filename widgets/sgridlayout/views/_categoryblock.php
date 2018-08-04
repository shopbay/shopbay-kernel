<?php echo CHtml::openTag('div', [
    'class'=>'sgridcategoryblock '.$element->name,
    'style'=>$element->style,
]);?>
    
    <?php if (strlen($element->getLanguageValue('title'))>0):?>
        <h2><?php echo $element->getLanguageValue('title');?></h2>
    <?php endif;?>
        
    <?php echo $element->renderListView(); ?>

<?php echo CHtml::closeTag('div');

$element->renderScript();

