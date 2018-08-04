<?php 
$this->widget('common.widgets.SDetailView', array(
    'data'=>array('name','total_spent','total_orders','last_order_id'),
    'columns'=>array(
        array(
            array('label'=>Customer::model()->getAttributeLabel('name'),'type'=>'raw','value'=>$data->shopLink),
            array('label'=>Customer::model()->getAttributeLabel('last_order'),'type'=>'raw','value'=>$data->lastOrderLink),
        ),
        array(
            array('label'=>Customer::model()->getAttributeLabel('total_orders'),'type'=>'raw','value'=>$data->total_orders),
        ),
        array(
            array('label'=>Customer::model()->getAttributeLabel('total_spent'),'type'=>'raw','value'=>$data->shop->formatCurrency($data->total_spent)),
        ),
    ),
));