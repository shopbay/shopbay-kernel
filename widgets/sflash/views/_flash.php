<div id="<?php echo isset($id)?$id:$this->id;?>" class="<?php echo isset($flash->type)?'flash-'.$flash->type:'flash-notice';?><?php echo isset($flash->theme)?' '.$flash->theme:'';?>">

    <?php if (!isset($flash->disableCloseButton)):?>
    <a class="close-button" href="javascript:void(0);" onclick="$(this).parent().fadeOut(300);" style="position:absolute">x</a>
    <?php endif;?>

    <?php if (isset($flash->title)):?>
        <div class="flash-title">
            <span class="flash-icon <?php echo $flash->type;?>">
                <?php echo $this->getFlashIcon($flash->type,isset($flash->icon)?$flash->icon:null);?>
            </span>
            <?php echo $flash->title;?>
        </div>
    <?php endif;?>

    <?php          
        if (isset($flash->message)){
            if (is_array($flash->message))
                var_export($flash->message);
            else
                echo $flash->message;
        }
    ?>

</div>
