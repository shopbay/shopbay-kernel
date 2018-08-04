<?php echo CHtml::openTag('div', ['class'=>'sgridboxblock '.$element->name,'style'=>$element->style]);?>
    
    <a id="link" data-field="link" data-field-type="link" class="form-field" href="<?php echo $element->link;?>">
        
        <img id="boxImage" data-field="boxImage" data-field-type="boxImage" class="form-field" src="<?php echo $element->boxImage;?>">
        
        <?php if (strlen($element->getLanguageValue('caption'))>0):?>
            <h3 id="caption" data-field="caption" data-field-type="text" class="caption form-field" >
                <?php echo $element->getLanguageValue('caption');?>
            </h3>
        <?php endif;?>
            
    </a>
    
<?php echo CHtml::closeTag('div');