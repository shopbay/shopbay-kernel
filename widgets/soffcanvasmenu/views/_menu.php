<div id="<?php echo $menu->id;?>" class="offcanvasmenu <?php echo $menu->openSide;?>">
    <div class="heading">
        <span class="title"><?php echo $menu->heading;?></span>
        <a href="javascript:void(0)" class="closebtn" onclick="<?php echo $menu->closeScript($pageId);?>">&times;</a>
    </div>
    <div class="menu-content">
        <?php echo $menu->content;?>
    </div>
</div>

