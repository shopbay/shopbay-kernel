<?php echo CHtml::openTag('div', ['class'=>'sgridmenublock '.$element->name,'style'=>$element->style]);?>
    
    <ul>
        <?php if (strlen($element->getLanguageValue('title'))>0):?>
            <h3><?php echo $element->getLanguageValue('title');?></h3>
        <?php endif;?>
            
        <?php foreach($element->menu as $menuitem):?>
            <li>
                <a href="<?php echo $menuitem['url'];?>">
                    <span><?php echo $element->getLanguageValue($menuitem['label'],true);?></span>
                </a>
            </li>
        <?php endforeach;?>
            
    </ul>    
    
<?php echo CHtml::closeTag('div');
