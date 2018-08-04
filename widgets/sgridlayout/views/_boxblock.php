<?php echo CHtml::openTag('div', ['class'=>'sgridboxblock '.$element->name,'style'=>$element->style]);?>

    <a href="<?php echo $element->link;?>">
        
        <img src="<?php echo $element->boxImage;?>" > 
            
        <?php if (strlen($element->getLanguageValue('caption'))>0):?>
            <h3 class="caption">
                <?php echo $element->getLanguageValue('caption');?>
            </h3>
        <?php endif;?>
            
    </a>

<?php echo CHtml::closeTag('div');