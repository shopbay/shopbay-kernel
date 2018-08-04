<?php 
$this->widget('common.widgets.SDetailView', array(
    'data'=>$data,
    'htmlOptions'=>array('class'=>'list-box float-image'),
    'attributes'=>array(
        array(
            'type'=>'raw',
            'template'=>'<div class="status">{value}</div>',
            'value'=>Helper::htmlColorText($data->getStatusText(),false),
        ),
        array(
            'type'=>'raw',
            'template'=>'<div class="image">{value}</div>',
            'value'=>$this->widget($this->module->getClass('listview'),array(
                    'dataProvider'=> $data->searchItems(),
                    'template'=>'{items}',
                    'emptyText'=>'',
                    'itemView'=>$this->module->getView('orders.orderproduct'),
                ),true),
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
                        'value'=>CHtml::link(CHtml::encode($data->order_no), $data->viewUrl),
                    ),
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="element">{value}</div>',
                        'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('grand_total')).'</strong>'.
                                 CHtml::encode($data->formatCurrency($data->grand_total)),
                    ),        
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="element">{value}</div>',
                        'value'=>$data->formatDatetime($data->create_time,true),
                    ),        
                ),
            ),true),
        ),        
    ),
)); 
