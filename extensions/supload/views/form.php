<!-- The file upload form used as target for the file upload widget -->
<?php if ($this->showForm) echo CHtml::beginForm($this -> url, 'post', $this -> htmlOptions);?>
<div class="row fileupload-buttonbar">

    <div class="span7">

            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn fileinput-button" style="font-size:1.1em;font-weight: bold">
                <i class="icon-plus icon-white"></i>
                <span><?php echo Sii::t('sii','1#Add|0#Choose Attachment', $this->multiple); ?></span>
                            <?php
                if ($this -> hasModel()) :
                    echo CHtml::activeFileField($this->model, $this->attribute, $htmlOptions) . "\n";
                else :
                    echo CHtml::fileField($name, $this->value, $htmlOptions) . "\n";
                endif;
                ?>
            </span>
            <span id="description" style="padding-left:10px">
                <?php echo CHtml::activeTextArea($this->model, 'description',array('rows'=>1,'cols'=>50)). "\n"; ?>
            </span>
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
