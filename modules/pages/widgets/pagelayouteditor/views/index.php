<p>
    <?php echo Sii::t('sii','You are editing page');?>
    <select id="editing_page">
        <?php echo $this->renderPageOptions($this->page);?>        
    </select>
    <?php echo Sii::t('sii','under theme');?>
    <select id="editing_theme">
        <?php echo $this->renderThemesOptions($this->controller->getPageOwnerThemes($this->page));?>        
    </select>
</p>

<p class="note">
    <?php echo Sii::t('sii','Please remember to save the layout if you have made changes.');?>
</p>

<div class="layout-editor" data-locale="<?php echo $this->locale;?>">
    
    <div class="workspace">
        <?php $this->layout->run(); ?>
    </div>
    
    <div class="palette">
        <div class="group">            
            <div class="heading"><span><?php echo Sii::t('sii','Layout');?></span></div>
            <div class="row-draggable">
              <?php echo (new SGridRowWidget($this->layout,$this->controller))->render(); ?>                
              <div class="widget-label" title="<?php echo Sii::t('sii','Row');?>">
                  <i class="fa fa-clone"></i>
              </div>
            </div>
            <div class="col-draggable">
                <?php echo (new SGridContainerBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Column Container');?>">
                    <i class="fa fa-columns"></i>
                </div>
            </div>
        </div>		
        
        <div class="group">
            <div class="heading"><span><?php echo Sii::t('sii','Widgets');?></span></div>
            <div class="col-draggable">
                <?php echo (new SGridHtmlBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Html Block');?>">
                    <i class="fa fa-code"></i>
                </div>
            </div>
            <div class="col-draggable">
                <?php echo (new SGridTextBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Text Block');?>">
                    <span style="font-family: serif">T</span>
                </div>
            </div>
            <div class="col-draggable">
                <?php echo (new SGridBoxBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Box Block');?>">
                    <i class="fa fa-square-o"></i>
                </div>
            </div>	
            <div class="col-draggable">
                <?php echo (new SGridImageBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Image Block');?>">
                    <i class="fa fa-image"></i>
                </div>
            </div>	
            <div class="col-draggable">
                <?php echo (new SGridSlideBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Slide Show');?>">
                    <i class="fa fa-play-circle-o"></i>
                </div>
            </div>
            <div class="col-draggable">
                <?php echo (new SGridCategoryBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Category Block');?>">
                    <i class="fa fa-sitemap"></i>
                </div>
            </div>
            <div class="col-draggable">
                <?php echo (new SGridListBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','List Block');?>">
                    <i class="fa fa-th-list"></i>
                </div>
            </div>
            <div class="col-draggable">
                <?php echo (new SGridMenuBlockWidget($this->layout,$this->controller))->render(); ?>                
                <div class="widget-label" title="<?php echo Sii::t('sii','Menu Block');?>">
                    <i class="fa fa-bars"></i>
                </div>
            </div>
        </div>		
    </div>    
    
    <div class="form-container">

        <?php 
            //Need this form to get csrf token
            $form=$this->beginWidget('CActiveForm', [
                'id'=>'layout_editor_form',
                'enableAjaxValidation'=>false,
            ]); 
        ?>

            <?php echo CHtml::hiddenField('page', $this->layout->themePage);?>
            <?php echo CHtml::hiddenField('theme', $this->theme->theme);?>
            <?php echo CHtml::hiddenField('name', $this->layout->pageLayout->name);?>

            <?php   
                $this->widget('zii.widgets.jui.CJuiButton',[
                    'name'=>'actionButton',
                    'buttonType'=>'button',
                    'caption'=>Sii::t('sii','Save'),
                    'value'=>'actionbtn',
                    'onclick'=>'js:function(){savepagelayout();}',
                    'htmlOptions'=>['class'=>'ui-button'],
                ]);
            ?>

            <?php  //only show when reset condition is met
                    if ($this->isPageResetable()){
                        $this->widget('common.widgets.SDetailView', [
                            'data'=>$this,
                            'htmlOptions'=>['class'=>'disclaimer'],
                            'columns'=>[
                                [
                                    ['label'=>Sii::t('sii','Page Reset'),'type'=>'raw','value'=>$this->getResetNotice()],
                                ],
                            ],
                        ]);
                    }
            ?>        

            <?php  $this->widget('common.widgets.SDetailView', [
                        'data'=>$this,
                        'htmlOptions'=>['class'=>'disclaimer'],
                        'columns'=>[
                            [
                                ['label'=>Sii::t('sii','Disclaimer'),'value'=>$this->disclaimer],
                            ],
                        ],
                    ]);
            ?>        

        <?php $this->endWidget(); ?>   

    </div>    
    
    <!-- Templates -->
    <div id="widget_dropdown_template" style="display:none;">
        <div class="config-menu">
            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <i class="fa fa-gear"></i>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                <li><a href="#" class="edit" data-toggle="modal" data-target="#widget_modal_template"><?php echo Sii::t('sii','Edit');?></a></li>
                <li><a href="#" class="delete"><?php echo Sii::t('sii','Delete');?></a></li>
            </ul>
        </div>        
    </div>   
    <?php  //Note: Remove modal tabindex="-1" to avoid conflict with ckedtior input (causing input fields not editable)  ?>
    <div id="widget_modal_template" class="modal" role="dialog" aria-labelledby="widgetModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content"></div>
        </div>
    </div>    
    <div id="widget_modal_flash_template" style="display:none;">
	<div class="alert alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>  		
        </div>
    </div>
    <div class="pageckeditor-asset" style="display:none;" data-url="<?php echo $this->controller->module->getAssetsURL($this->controller->module->pathAlias.'.js').'/'.$this->controller->module->getAssetFilename('pageckeditor.js');?>"></div>
    <div class="pageckeditor-imageupload" style="display:none;" data-url="<?php echo '/'.$this->controller->getActionRoute('ckeditorimageupload');?>"></div>
    <div class="layouteditor-category" style="display:none;" data-url="<?php echo '/'.$this->controller->getActionRoute('category');?>"></div>
    <div class="layouteditor-listitem" style="display:none;" data-url="<?php echo '/'.$this->controller->getActionRoute('listItem');?>"></div>
    <div class="layouteditor-update" style="display:none;" data-url="<?php echo $this->controller->getUpdateUrl();?>"></div>
    
</div>

<?php
$this->setControllerLayout();