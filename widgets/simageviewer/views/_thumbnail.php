<span style="float:left; padding:2px 2px">
    <a class="thumbnail" id="image<?php echo $data->id;?>" href="javascript:swapimage('<?php echo $cssClass?>',<?php echo $data->id?>);">
        <?php echo $data->render($thumbnailVersion,Sii::t('sii','Image')); ?>
    </a> 
</span>           

