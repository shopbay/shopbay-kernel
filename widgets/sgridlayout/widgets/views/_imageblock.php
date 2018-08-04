<?php echo CHtml::openTag('div', ['class'=>'sgridimageblock '.$element->name,'style'=>$element->getStyleWithBgImage()]);?>
    
    <div class="content-wrapper">
        
	<?php if (strlen($element->getLanguageValue('title'))>0):?>
            <h2 id="title" data-field="title" data-field-type="text" class="form-field"><?php echo $element->getLanguageValue('title');?></h2>
	<?php endif;?>
	<?php if (strlen($element->getLanguageValue('desc'))>0):?>
            <p id="desc" data-field="desc" data-field-type="text" class="form-field" ><?php echo $element->getLanguageValue('desc');?></p>
	<?php endif;?>
    </div>

    <?php if (strlen($element->ctaUrl)>0):?>
    <div class="link-wrapper">
        <a id="ctaUrl" data-field="ctaUrl" data-field-type="link" href="<?php echo $element->ctaUrl;?>">
            <span id="ctaLabel" data-field="ctaLabel" data-field-type="text" class="form-field" ><?php echo $element->getLanguageValue('ctaLabel');?></span>
        </a>
    </div>
    <?php endif;?>
    
<?php echo CHtml::closeTag('div');