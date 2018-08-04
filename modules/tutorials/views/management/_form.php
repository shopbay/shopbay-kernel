<?php cs()->registerScript('chosen','$(\'.chzn-select\').chosen();$(\'.chzn-search\').hide();',CClientScript::POS_END);?>
<?php cs()->registerScript('chosen2','$(\'.chzn-select-tags\').chosen();',CClientScript::POS_END);?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>'tutorial-form',
        'enableAjaxValidation'=>false,
)); ?>

        <?php if ($model->isNewRecord):?>
        <p class="note"><?php echo Sii::t('sii','Fields with <span class="required">*</span> are required.');?></p>
        <?php endif;?>

        <?php echo $form->errorSummary($model,null,null,array('style'=>'width:57%')); ?>

        <div class="row">
            <?php $model->renderForm($this);?>
        </div>

        <div class="row" style="margin-top:20px;">
            <?php echo $form->labelEx($model,'difficulty',array('style'=>'margin-bottom:3px;')); ?>
            <?php echo $form->dropDownList($model,'difficulty', 
                                            Tutorial::getDifficultyLevels(), 
                                            array('prompt'=>'',
                                                  'class'=>'chzn-select',
                                                  'data-placeholder'=>Sii::t('sii','Select Difficulty'),
                                                  'style'=>'width:250px;'));
            ?>
            <?php //echo $form->error($model,'difficulty'); ?>
	</div>
            
        
        <div class="row" style="margin-top:20px;">
            <?php echo $form->labelEx($model,'tags',array('style'=>'margin-bottom:5px;')); ?>
            <?php echo $form->dropDownList($model, 'tags',
                                           Tag::getList(user()->getLocale()),  
                                           array('prompt'=>'',
                                                 'class'=>'chzn-select-tags',
                                                 'multiple'=>true,
                                                 'data-placeholder'=>Sii::t('sii','Select Tags'),
                                                 'style'=>'width:60%;'));
            ?>
            <?php echo $form->error($model,'tags'); ?>
        </div>

        <?php if (user()->hasRole(Role::ADMINISTRATOR)):?>
        <div class="seo-section">
            <h2><?php echo Sii::t('sii','Search Engine Optimization');?></h2>
            
            <div class="row page-label">
                <?php echo $form->labelEx($model,'seoTitle'); ?>
                <?php $this->stooltipWidget($model->getToolTip('seoTitle')); ?>
                <p>
                    <?php echo $form->textField($model,'seoTitle',['size'=>95,'maxlength'=>TutorialForm::$pageTitleLength,'placeholder'=>Sii::t('sii','Maximum {n} characters',['{n}'=>TutorialForm::$pageTitleLength])]); ?>
                    <?php echo $form->error($model,'seoTitle'); ?>
                </p>
            </div>            
            <div class="row page-label">
                <?php echo $form->labelEx($model,'seoDesc'); ?>
                <?php $this->stooltipWidget($model->getToolTip('seoDesc')); ?>
                <p>
                    <?php echo $form->textArea($model,'seoDesc',['rows'=>3,'maxlength'=>TutorialForm::$metaDescLength,'placeholder'=>Sii::t('sii','Maximum {n} characters',['{n}'=>TutorialForm::$metaDescLength])]); ?>
                    <?php echo $form->error($model,'seoDesc'); ?>
                </p>
            </div>            
            <div class="row page-label">
                <?php echo $form->labelEx($model,'seoKeywords'); ?>
                <?php $this->stooltipWidget($model->getToolTip('seoKeywords')); ?>
                <p>
                    <?php echo $form->textArea($model,'seoKeywords',['rows'=>3,'maxlength'=>TutorialForm::$metaKeywordsLength,'placeholder'=>Sii::t('sii','Maximum {n} characters',['{n}'=>TutorialForm::$metaKeywordsLength])]); ?>
                    <?php echo $form->error($model,'seoKeywords'); ?>
                </p>
            </div>          
            
        </div>
        <?php endif;?>
        
        <div class="row" style="margin-top:20px;">
            <?php 
                $this->widget('zii.widgets.jui.CJuiButton',
                    array(
                        'name'=>'actionButton',
                        'buttonType'=>'button',
                        'caption'=> Sii::t('sii','Save'),
                        'value'=>'actionbtn',
                        'onclick'=>'js:function(){submitform(\'tutorial-form\');}',
                        )
                );
             ?>
        </div>

<?php $this->endWidget(); ?>

</div><!-- form -->