<div id="<?php echo $this->getId();?>" class="tooltips <?php echo $this->getPosition();?>" href="#">
    <?php echo $this->symbol;?>
    <?php echo CHtml::tag('div',
            array('class'=>$this->getCssClass()),
            $this->content);?>
</div>
<?php 
if ($this->autoTop && ($this->getPosition()==SToolTip::POSITION_LEFT||$this->getPosition()==SToolTip::POSITION_RIGHT))
    Yii::app()->clientScript->registerScript('stooltip'.$this->getId(),'$("#'.$this->getId().' .'.$this->getCssClass().'").css({top:-$("#'.$this->getId().' .'.$this->getCssClass().'").height()*0.21})');
