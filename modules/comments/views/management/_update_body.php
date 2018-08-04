<?php
/* @var $this ManagementController */
/* @var $model Comment */
/* @var $form CActiveForm */
?>
<div class="data-form">

    <div class="form">

        <?php $form=$this->beginWidget('CActiveForm', array(
                'id'=>'comment-form',
                'action'=>($model instanceof CommentForm)? $model->getReviewUrl():url('comments/management/update',array('id'=>$model->id)),
                'enableAjaxValidation'=>false,
        )); ?>

                <?php if ($model instanceof CommentForm){
                          echo $form->hiddenField($model,'type');
                          echo $form->hiddenField($model,'target');
                          echo $form->hiddenField($model,'src_id'); 
                      }
                      else {
                          echo $form->hiddenField($model,'obj_id');
                          echo $form->hiddenField($model,'obj_type');
                      }
                ?>

                <?php //echo $form->errorSummary($model); ?>

                <?php if (($model instanceof CommentForm) || $model->rating!=null): ?>
                <div class="row">
                        <?php echo $form->label($model,'rating'); ?>
                        <p>
                         <?php $this->widget('CStarRating',array('id'=>'rating','name'=>get_class($model).'[rating]','value'=>$model->rating)); ?>
                        </p>
                        <?php echo $form->error($model,'rating'); ?>
                </div>
                <?php endif;?>

                <div class="row" style="margin-top: 45px">
                        <?php echo $form->label($model,'content'); ?>
                        <?php echo $form->textArea($model,'content',array('rows'=>6, 'cols'=>50,'style'=>'font-size:1.2em;overflow-y:auto;')); ?>
                        <?php echo $form->error($model,'content'); ?>
                </div>

                <div class="row" style="margin-top: 20px">
                <?php $this->widget('zii.widgets.jui.CJuiButton',array(
                                    'name'=>'comment-button',
                                    'buttonType'=>'button',
                                    'caption'=>$model->isNewRecord ? Sii::t('sii','Post') : Sii::t('sii','Change'),
                                    'value'=>'commentBtn',
                                    'onclick'=>'js:function(){submitform(\'comment-form\');}',                                    
                                )); 
                ?>
                </div>

         <?php $this->endWidget(); ?>

     </div><!-- form -->

</div>
