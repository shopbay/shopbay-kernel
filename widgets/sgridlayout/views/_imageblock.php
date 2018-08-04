<?php echo CHtml::openTag('div', ['class'=>'sgridimageblock '.$element->name,'style'=>$element->style]);?>

    <div class="content-wrapper">
        <?php if (strlen($element->getLanguageValue('title'))>0):?>
            <h2><?php echo $element->getLanguageValue('title');?></h2>
        <?php endif;?>
        <?php if (strlen($element->getLanguageValue('desc'))>0):?>
            <p><?php echo $element->getLanguageValue('desc');?></p>
        <?php endif;?>
    </div>

    <?php if (!empty($element->cta['label']) && strlen($element->getLanguageValue($element->cta['label'],true))>0):?>
    <div class="link-wrapper">
        <a href="<?php echo $element->cta['url'];?>">
            <?php echo $element->getLanguageValue($element->cta['label'],true);?>
        </a>
    </div>
    <?php endif;?>

<?php echo CHtml::closeTag('div');