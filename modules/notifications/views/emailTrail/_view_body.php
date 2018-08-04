<?php 
$this->widget('common.widgets.SDetailView', array(
    'data'=>$model,
    'columns'=>array(
        array(
            array('label'=>Sii::t('sii','Send Mode'),'type'=>'raw','value'=>$model->data->mode),
            array('label'=>Sii::t('sii','Address To'),'type'=>'raw','value'=>$model->data->addressTo),
            array('label'=>Sii::t('sii','Address Name'),'type'=>'raw','value'=>$model->data->addressName),
            array('name'=>'create_time','value'=>date('Y-m-d h:i:s',$model->create_time)),
            array('name'=>'update_time','value'=>date('Y-m-d h:i:s',$model->update_time)),
            array('label'=>Sii::t('sii','Subject'),'type'=>'raw','value'=>$model->data->subject),
        ),
    ),
));
?>
<div class="email-body">
    <?php echo $model->data->content;?>
</div>
