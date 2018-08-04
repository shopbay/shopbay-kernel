<div class="object-thumbnail">
    <span class="caption">
        <?php if (isset($data->getAccountOwner()->status))
                  echo Helper::htmlColorText($data->getAccountOwner()->getStatusText()); 
        ?>
    </span>
    <span class="caption2"><?php echo $data->displayName(); ?></span>
    <?php 
        if ($data->getAccountOwner()->hasBehaviors('multilang'))
            $title = $data->getAccountOwner()->displayLanguageValue('name',user()->getLocale());
        else
            $title = '';
        
        echo CHtml::link($data->getAccountOwner()->getImageThumbnail(Image::VERSION_XSMALL,array('class'=>'img','title'=>$title)),$data->viewUrl);
    ?>    
</div>