<div id="page-menu-<?php echo $this->id;?>" class="<?php echo $this->cssClass;?>">
    <?php
        echo CHtml::form(url('menu'),'post',array('id'=>'menu-form'));
        $this->widget('zii.widgets.CMenu', array(
                    'items'=>$this->getMenu(),
                    'encodeLabel'=>false,
                    'htmlOptions'=>array('class'=>'pagemenu'),
        ));
        echo CHtml::endForm(); ?> 
</div>
