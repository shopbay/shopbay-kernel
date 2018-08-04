<?php $form=$this->beginWidget('CActiveForm', [
        'id'=>'prev_comment_form',
        //'action'=>url('comments/management/create'),
        'enableAjaxValidation'=>false,
    ]); 
?>

    <?php echo $form->hiddenField($commentForm,'target'); ?>
    <?php echo $form->hiddenField($commentForm,'type'); ?>
    <?php echo CHtml::hiddenField('CommentForm[page]',($commentForm->page+1)); ?>

    <?php echo CHtml::link(isset($message)?$message:Sii::t('sii','View previous comments'), 'javascript:void(0);',['onclick'=>'prevdata(\'comment\',\''.$route.'\')']);?>

    <div class="comment-loader-wrapper">
        <?php 
            $this->widget('common.widgets.sloader.SLoader',[
                'id'=>'comment_loader',
                'type'=>SLoader::RELATIVE,
            ]);
        ?>    
    </div>

<?php $this->endWidget(); ?>