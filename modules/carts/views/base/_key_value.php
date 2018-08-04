<div class="<?php echo $data['cssClass'];?>">
    <?php if ($data['key']!=false):?>
    <div class="key"><?php echo $data['key'];?></div>
    <?php endif;?>
    <div class="value" id="<?php echo $data['id'];?>">
        <?php echo $data['value'];?>
    </div>    
</div>
