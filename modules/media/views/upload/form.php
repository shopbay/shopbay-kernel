<!-- The file upload form used as target for the file upload widget -->
<?php if ($this->showForm) echo CHtml::beginForm($this->url, 'post', $this->htmlOptions);?>
<div class="row fileupload-buttonbar">

    <div class="span7">
        <!-- The fileinput-button span is used to style the file input field as button -->
        <span class="btn btn-success fileinput-button" style="font-size:1em;font-weight: bold;margin-bottom: 2px;">
            <i class="icon-plus icon-white"></i>
            <span><?php echo $this->model->getUploadButtonText($this->multiple); ?></span>
            <?php if ($this->hasModel()):
                      echo CHtml::activeFileField($this->model, $this->attribute, $htmlOptions) . "\n";
                  else:
                      echo CHtml::fileField($name, $this->value, $htmlOptions) . "\n";
                  endif;
            ?>
        </span>
        <div id="description" style="<?php echo $this->model->disableDescription?'display:none;':'clear:both;';?>">
            <?php echo CHtml::activeTextArea($this->model, 'description',array('rows'=>1,'cols'=>42,'maxlength'=>30,'style'=>'resize:none','placeholder'=>$this->htmlOptions['placeholder'])). "\n"; ?>
        </div>
    </div>

    <div class="span5">
        <!-- The global progress bar -->
        <div class="progress progress-success progress-striped active fade">
            <div class="bar" style="width:0%;"></div>
        </div>
    </div>
</div>
<!-- The loading indicator is shown during image processing -->
<div class="fileupload-loading"></div>
<br>
<!-- The table listing the files available for upload/download -->
<table>
    <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
</table>
<?php if ($this->showForm) echo CHtml::endForm();?>