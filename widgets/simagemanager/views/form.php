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
    <table class="<?php echo $this->hasImages?'items':'no-items'; ?>">
        <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">
            <?php if ($this->hasImages){
                      echo $this->renderImages();
                      cs()->registerScript(__CLASS__,$this->model->getDeleteButtonScript(),CClientScript::POS_END);
                  }
                  echo CHtml::openTag('tr',array('class'=>'empty','style'=>$this->hasImages?'display:none':''));
                  echo CHtml::tag('td',array(),$this->getEmptyText());
                  echo CHtml::closeTag('tr');
                  echo CHtml::openTag('tr',array('class'=>'file-upload-progress','style'=>'display:none'));
                  echo CHtml::tag('td',array('colspan'=>4),CHtml::tag('div',array('class'=>'bar','style'=>'width:0%;'),''));
                  echo CHtml::closeTag('tr');
            ?>
        </tbody>
    </table>
</div>
<?php if ($this->showForm) 
        echo CHtml::endForm();
