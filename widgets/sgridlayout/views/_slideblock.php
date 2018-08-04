<?php echo CHtml::openTag('div', ['class'=>'sgridslideblock '.$element->name,'style'=>$element->style]);?>
    <?php 
        echo bootstrap()->Carousel(['items'=>$element->items]);
    ?>
<?php echo CHtml::closeTag('div');