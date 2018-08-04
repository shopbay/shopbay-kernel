<div class="form">

<?php $form=$this->beginWidget('CActiveForm', [
        'id'=>'page_form',
        'enableAjaxValidation'=>false,
]); ?>

        <?php if ($model->isNewRecord):?>
            <p class="note"><?php echo Sii::t('sii','Fields with <span class="required">*</span> are required.');?></p>
        <?php endif;?>

        <?php //echo $form->errorSummary($model); ?>

        <div class="row">
            <?php $model->renderForm($this);?>
        </div>

        <div class="seo-section">
            <h2><?php echo Sii::t('sii','Search Engine Optimization');?></h2>
            
            <div class="row page-label">
                <?php echo $form->labelEx($model,'slug'); ?>
                <?php $this->stooltipWidget($model->getToolTip('slug')); ?>
                <p class="note"><?php echo $model->pageBaseUrl.'/'; ?>
                    <?php echo $form->textField($model,'slug',['size'=>50,'maxlength'=>100,'disabled'=>!$model->isNewRecord,'class'=>!$model->isNewRecord?'disabled':'enabled']); ?>
                </p>
            </div>
            
            <?php if ($model->hasSEOConfigurator()):?>
            <div class="row page-label">
                <?php echo $form->labelEx($model,'seoTitle'); ?>
                <?php $this->stooltipWidget($model->getToolTip('seoTitle')); ?>
                <p>
                    <?php echo $form->textField($model,'seoTitle',['size'=>95,'maxlength'=>PageForm::$pageTitleLength,'placeholder'=>Sii::t('sii','Maximum {n} characters',['{n}'=>PageForm::$pageTitleLength])]); ?>
                    <?php echo $form->error($model,'seoTitle'); ?>
                </p>
            </div>            
            <div class="row page-label">
                <?php echo $form->labelEx($model,'seoDesc'); ?>
                <?php $this->stooltipWidget($model->getToolTip('seoDesc')); ?>
                <p>
                    <?php echo $form->textArea($model,'seoDesc',['rows'=>3,'maxlength'=>PageForm::$metaDescLength,'placeholder'=>Sii::t('sii','Maximum {n} characters',['{n}'=>PageForm::$metaDescLength])]); ?>
                    <?php echo $form->error($model,'seoDesc'); ?>
                </p>
            </div>            
            <div class="row page-label">
                <?php echo $form->labelEx($model,'seoKeywords'); ?>
                <?php $this->stooltipWidget($model->getToolTip('seoKeywords')); ?>
                <p>
                    <?php echo $form->textArea($model,'seoKeywords',['rows'=>3,'maxlength'=>PageForm::$metaKeywordsLength,'placeholder'=>Sii::t('sii','Maximum {n} characters',['{n}'=>PageForm::$metaKeywordsLength])]); ?>
                    <?php echo $form->error($model,'seoKeywords'); ?>
                </p>
            </div>          
            <?php endif;?>
            
        </div>
            
        <div class="row" style="margin-top:20px;">
            <?php   $this->widget('zii.widgets.jui.CJuiButton',[
                        'name'=>'actionButton',
                        'buttonType'=>'button',
                        'caption'=>$model->isNewRecord ? Sii::t('sii','Create') : Sii::t('sii','Save'),
                        'value'=>'actionbtn',
                        'onclick'=>'js:function(){submitform(\'page_form\');}',
                    ]);
            ?>
        </div>

<?php $this->endWidget(); ?>

</div>
