<!-- The file upload form used as target for the file upload widget -->
<?php if ($this->showForm) echo CHtml::beginForm($this->url, 'post', $this->htmlOptions);?>
<div class="row fileupload-buttonbar">
    <div>
        <?php echo $this->getFormLabel();?>
        <div class="options">
            <?php if ($this->enableMediaGalleryForm()):?>
            <span class="btn media-gallery">
                <a href="javascript:void(0)" onclick="<?php echo $this->mediaGalleryScript.'(\''.$this->mediaGalleryFormGetUrl.'\');';?>"><?php echo Sii::t('sii','Media Gallery');?></a> 
            </span>
            <?php endif;?>
            <?php if ($this->enableUrlForm()):?>
            <span class="btn by-url">
                <a href="javascript:void(0)" onclick="<?php echo $this->urlFormScript.'(\''.$this->urlFormGetUrl.'\');'?>"><?php echo Sii::t('sii','By URL');?></a> 
            </span>
            <?php endif;?>
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn fileinput-button">
                <i class="icon-plus icon-white"></i>
                <span><?php echo Sii::t('sii','Upload');?></span>
                <?php
                    if ($this->hasModel()):
                        echo CHtml::activeFileField($this->model, $this->attribute) . "\n";
                    else :
                        echo CHtml::fileField($name, $this->value) . "\n";
                    endif;
                ?>
            </span> 
        </div>
    </div>
</div>
<!-- The loading indicator is shown during image processing -->
<div class="fileupload-loading"></div>
<br>
<!-- The div listing the files available for upload/download -->
<div class="files single-image-container" data-toggle="modal-gallery" data-target="#modal-gallery">
    <div class="template-download empty">
        <div class="preview">
            <?php echo $this->model->parent->getDefaultImage(); ?>
        </div>     
    </div>
    <!-- The progress bar -->
    <div class="file-upload-progress" style="display:none;">
        <div class="bar" style="width:0%;"></div>
    </div>
</div>
<?php 
if ($this->showForm) 
    echo CHtml::endForm();

