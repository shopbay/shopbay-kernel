<?php echo CHtml::openTag('div', [
    'class'=>'sgridlistblock '.$element->name,
    'style'=>$element->style,
    'data-item-script'=>$element->itemScript,
]);?>

    <?php echo $element->renderListView(); ?>

<?php echo CHtml::closeTag('div');

$element->renderScript();
