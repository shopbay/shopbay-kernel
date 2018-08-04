<?php if ($this->showThumbnail && $this->hasMultipleImages): ?>
<div class="thumbnails">
    <?php $this->widget('zii.widgets.CListView', array(
            'dataProvider'=> $this->imageModel->searchImages(),
            'itemView'=>'common.widgets.simageviewer.views._thumbnail',
            'viewData'=>array(
                'thumbnailVersion'=>$this->thumbnailVersion,
                'cssClass'=>$this->cssClass,
            ),
            'template'=>'{items}',
        ));        
    ?>
</div>    
<?php endif; ?>
<div class="<?php echo $this->cssClass;?>" style="min-width:<?php echo $this->imageVersion;?>px;text-align: left;">
    <a rel="<?php echo $this->cssClass;?>" id="picture-<?php echo $this->imageModel->image;?>" href="<?php echo $this->imageModel->getImageUrl(Image::VERSION_ORIGINAL); ?>">
        <?php echo $this->getImageThumbnail(); ?>
    </a>
    <?php if ($this->hasMultipleImages): ?>
        <?php  foreach ($this->imageModel->searchImages()->data as $image):?>
            <?php if ($image->id!=$this->imageModel->image): ?>
                <a rel="<?php echo $this->cssClass;?>" id="picture-<?php echo $image->id;?>" href="<?php echo $image->getUrl(); ?>" style="display:none">
                    <?php echo $image->render($this->imageVersion,'Image'); ?>
                </a>
            <?php endif; ?>
        <?php endforeach;?>
    <?php endif; ?>
</div>
