<?php
    $this->widget('common.widgets.SDetailView', [
        'data'=>$model,
        'columns'=>[
            [
                //['label'=>Sii::t('sii','Account Name'),'value'=>$model->name],
                ['name'=>'create_time','label'=>Sii::t('sii','Member since'),'value'=>$model->formatDatetime($model->create_time,true)],
                ['label'=>Sii::t('sii','Last update time'),'value'=>$model->formatDatetime($model->profile->update_time,true)],
  //              ['label'=>Sii::t('sii','Merchant Account'),'value'=>Sii::t('sii','Yes'),'visible'=>user()->hasRole(Role::MERCHANT)],
    //            ['label'=>Sii::t('sii','Email'),'value'=>$model->email],
            ],
        ],
    ]);
?>
<div class="image-form" >
    <?php $this->renderImageForm(
            $model->profile,
            CHtml::label(Sii::t('sii','Change Avatar'),''),
            url($this->module->id .'/profile/'.$this->imageUploadAction));
    ?>    
</div>
<div class="data-form">
    <?php echo $this->renderPartial('_profile_form', array('model'=>$model)); ?>
</div>

<?php if (user()->hasRoleTask(Task::ACCOUNT_CLOSURE)):?>
<div class="close-form">
    <?php if (!user()->isSuperuser)
              echo $this->renderPartial('_closed_form');
    ?>
</div>
<?php endif;?>