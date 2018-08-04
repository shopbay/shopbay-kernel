<?php if (strlen($element->getLanguageValue('title'))>0):?>
    <h2><?php echo $element->getLanguageValue('title');?></h2>
<?php endif;?>
<?php 
    foreach ($element->getRowElements() as $row) {
        echo $row->render();
    }
