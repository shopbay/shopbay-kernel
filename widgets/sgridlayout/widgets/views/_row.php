<?php 
    echo CHtml::openTag('div', array_merge([
            'class'=>'widget-sgridrow '.$element->type.' '.$element->name,
        ],$element->getElementDataArray()));
?>

    <?php  echo $element->content; ?>

<?php echo CHtml::closeTag('div');