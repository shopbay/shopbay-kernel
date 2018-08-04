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
    <div>
        <!-- The global progress bar -->
        <div class="progress progress-success progress-striped active fade">
            <div class="bar" style="width:0%;"></div>
        </div>
    </div>
</div>
<div class="fileupload-loading"></div>

<div class="grid-view images-gallery" style="width:100%;overflow-x:auto">
    <table class="items">
        <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">
            <tr class="template-download empty" style="<?php echo $this->model->parent->hasImages ? 'display:none;': '';?>">
                <td width="100%" class="preview">
                    <?php echo $this->model->parent->getDefaultImage(); ?>
                </td>
            </tr>
            <?php 
                echo $this->model->parent->loadImageRowTemplate(); //must load a template for adding new row use
                if ($this->model->parent->hasImages)
                    echo $this->model->parent->loadImageRow(); 
            ?>
        </tbody>
    </table>
</div>
<?php if ($this->showForm) 
        echo CHtml::endForm();
