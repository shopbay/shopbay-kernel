<?php
$this->widget('common.widgets.SDetailView', array(
    'data'=>$data,
    'htmlOptions'=>array('class'=>'list-box'),
    'attributes'=>array(
        array(
            'type'=>'raw',
            'template'=>'<div class="element">{value}</div>',
            'value'=>$data->primaryKey,
        ),
//        array(
//            'type'=>'raw',
//            'template'=>'<div class="heading-element">{value}</div>',
//            'value'=>$data->name,
//        ),
    ),
)); 