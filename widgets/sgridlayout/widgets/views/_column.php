<?php 
    echo CHtml::openTag('div', array_merge([
            'class'=>'widget-sgridcolumn '.$element->type.' '.$element->name.' col-md-'.$element->size,
        ],$element->getElementDataArray()));
?>

    <?php 
        echo $element->content; 
    ?>

<?php echo CHtml::closeTag('div');