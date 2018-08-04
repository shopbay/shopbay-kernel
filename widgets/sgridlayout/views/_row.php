<?php echo CHtml::openTag('div', array_merge([
            'class'=>'row '.$element->name.' sgridrow',
            'style'=>$element->style,
        ],$element->getElementDataArray()));
?>
    <?php 
        foreach ($element->getColumnElements() as $column) {
            echo $column->render();
        }
    ?>

    <?php  echo $element->content; ?>

<?php echo CHtml::closeTag('div');