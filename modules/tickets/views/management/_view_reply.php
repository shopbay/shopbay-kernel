<?php
if (!empty($data))
    $this->widget('common.widgets.SDetailView', array(
        'id'=>'ticket_reply',
        'data'=>array('content'),
        'attributes'=>$data,
        'htmlOptions'=>array('class'=>isset($cssClass)?$cssClass:'detail-view rounded'),
    ));    
else 
    echo CHtml::tag('div',array('class'=>'detail-view rounded','id'=>'ticket_reply','style'=>'display:none'),'');
