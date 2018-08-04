<a href="<?php echo $orderUrl;?>" style="display:block;color:white;text-align:center;width:100%;background:lightskyblue;font-size:3em;margin-bottom:20px;padding: 5px 0px;text-decoration: none;">
    <?php echo Notification::getActionLabel($model,isset($role)?$role:null);?>
</a>