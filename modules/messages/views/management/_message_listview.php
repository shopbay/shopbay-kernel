<?php $this->widget('common.widgets.SDetailView', array(
        'data'=>$data,
        'htmlOptions'=>array('class'=>'list-box'),
        'attributes'=>array(
            array(
                'type'=>'raw',
                'template'=>'<div class="image">{value}</div>',
                'value'=>CHtml::image($data->receive_time==null?$this->getImage('mail_close.png'):$this->getImage('mail_open.png'),'',array('style'=>'margin-left:5px;margin-top:5px;')),
            ),
            array(
                'type'=>'raw',
                'template'=>'{value}',
                'value'=>$this->widget('common.widgets.SDetailView', array(
                    'data'=>$data,
                    'htmlOptions'=>array('class'=>'data'),
                    'attributes'=>array(
                        array(
                            'type'=>'raw',
                            'template'=>'<div class="heading-element">{value}</div>',
                            'value'=>CHtml::link(CHtml::encode($data->getSubject()), $data->viewUrl,
                                       array('style'=>($data->receive_time==null?'font-weight:bold':'font-weight:normal'))
                                    ),
                        ),
                        array(
                            'type'=>'raw',
                            'template'=>'<div class="element">{value}</div>',
                            'value'=>Helper::prettyDate($data->send_time),
                        ),        
                    ),
                ),true),
            ),        
        ),
    )); 