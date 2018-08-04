<div id="<?php echo $this->getId();?>" class="tooltips" data-toggle="tooltip" data-placement="<?php echo $this->placement;?>" title="<?php echo $this->content;?>">
    <?php echo $this->symbol;?>
</div>
<?php 
Yii::app()->clientScript->registerScript('sbootstraptooltip'.$this->getId(),'$("#'.$this->getId().'").tooltip();');
