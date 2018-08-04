<?php
/* @var $this ManagementController */
/* @var $data Tutorial */
?>
<div class="list-box">
    <span class="status">
        <?php echo Helper::htmlColorText($data->getStatusText(),false); ?>
    </span>
    <?php $this->widget('common.widgets.SDetailView', array(
            'data'=>$data,
            'htmlOptions'=>array('class'=>'data'),
            'attributes'=>array(
                array(
                    'type'=>'raw',
                    'template'=>'<div class="status">{value}</div>',
                    'value'=>Helper::htmlColorText($data->getStatusText(),false),
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>CHtml::link(CHtml::encode($data->subject), $data->viewUrl),
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>CHtml::encode($data->account->name),
                    'visible'=>$this->isAdminApp,
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>CHtml::encode($data->formatDatetime($data->create_time,false)),
                ),
            ),
        )); 
    ?> 
</div>