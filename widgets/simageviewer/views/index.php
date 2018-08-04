<div id="<?php echo $this->getId();?>" class="imgContainer" style="min-width:<?php echo $this->hasMultipleImages?($this->imageVersion+$this->thumbnailVersion+10).'px':'auto';?>;" >
    <?php if ($this->hasImageModel):?>
        <?php $this->render('_body');?>
    <?php elseif (isset($this->imageUrl)):?>
    <div class="<?php echo $this->cssClass;?>" style="min-width:<?php echo $this->imageVersion;?>px;text-align: left;">
        <a rel="<?php echo $this->cssClass;?>" id="picture-<?php echo $this->getId();?>" href="<?php echo $this->imageUrl; ?>">
            <?php echo $this->getImageThumbnail(); ?>
        </a>
    </div>
    <?php endif;?>
</div>
<?php 
Yii::app()->clientScript->registerScript('simageviewer','loadfancybox("'.$this->cssClass.'","'.$this->getImageName().'");');