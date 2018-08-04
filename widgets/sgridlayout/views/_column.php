<?php echo CHtml::openTag('div', array_merge([
            'class'=>$element->cssClass.' '.$element->name.' sgridcolumn',
//            'style'=>$element->style,//do not repeat style here as each column block has own style
        ],$element->getElementDataArray()));
?>
    <?php 
        echo $element->content; 
    ?>
<?php echo CHtml::closeTag('div');