<?php if (isset($this->name)):?>
    <div class="page-tab-container-heading"><?php echo $this->name;?></div>
<?php endif;?>
<div id="page_tab_container_<?php echo $this->id;?>" class="page-tab-container">
    <?php
        $this->widget('CTabView', array(
                'id'=>$this->id,
                'htmlOptions'=>array('class'=>'page-tab'),
                'tabs'=>$this->getTabs(),
                'cssFile'=>false,
            ));
    ?> 
</div>
