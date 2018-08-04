<div class="susermenu <?php echo $this->type;?> <?php echo $this->cssClass;?>">
    <?php   
            $this->widget('zii.widgets.CMenu', [
                'encodeLabel'=>false,
                'items'=>$menu,
                'htmlOptions'=>['class'=>($this->offCanvas?'offcanvas-menu ':'').$this->type],
            ]);
    ?>    
</div>
