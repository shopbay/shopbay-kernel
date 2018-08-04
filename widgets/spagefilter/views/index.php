<div id="<?php echo $this->id;?>" class="<?php echo $this->cssClass;?>">

    <div class="heading">
        <?php if (!empty($this->quickMenu)): ?>
            <?php $this->widget('SPageMenu', array('items'=>$this->quickMenu)); ?>
        <?php endif; ?>

        <h1>
            <i class='fa fa-search'></i>
            <?php echo Sii::t('sii','Search')?>
        </h1>
    </div>
    
    <div class="form">

    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>$model->formId,
            'action'=>$model->actionUrl,
            'enableAjaxValidation'=>false,
    )); ?>

        <?php foreach ($model->fields as $field => $value):?>
            <div class="row">
                <?php echo $model->renderField($field,$value);?>
                <?php echo $form->error($model,$field); ?>
            </div>
        <?php endforeach;?>

    <?php $this->endWidget(); ?>

    </div><!-- form -->    
    
</div>