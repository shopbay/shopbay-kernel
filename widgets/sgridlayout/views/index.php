<div class="<?php echo $this->id;?> <?php echo $this->container;?> sgridlayout">
    <?php 
        foreach ($this->rows as $row) {
            echo $row->render();
        }
    ?>
</div> 