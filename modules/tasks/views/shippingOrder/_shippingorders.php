<?php $this->widget($this->getModule()->getClass('groupview'), array(
    'dataProvider' => $dataProvider,
    //'filter'=>$searchModel,
    'mergeColumns' => array('order_no','shop_id'),  
    'columns'=>array(
        array(
           'name'=>'create_time',
           'value'=>'$data->formatDateTime($data->create_time,true)',
           'htmlOptions'=>array('style'=>'text-align:center;width:12%;'),
         ),
        array(
           'name'=>'shipping_no',
           'header'=> Sii::t('sii','Shipping'),
           'value'=>'\'<div>\'.$data->shipping_no.\'</div><div>\'.$data->getShippingName(user()->getLocale()).\'</div><div>\'.$data->formatCurrency($data->grand_total).\'</div>\'',
           'type'=>'raw',
           'htmlOptions'=>array('style'=>'text-align:center;width:10%;','class'=>'order'),
        ),
        array(
            'name'=>'item_count',//use id as proxy for item name search
            'class' =>$this->getModule()->getClass('itemcolumn'),
            'label' => Sii::t('sii','Products'),
            'value' => '$data->getItemColumnData()',
        ),
        //array(
        //    'name'=>'item_shipping',
        //    'header'=> Sii::t('sii','Shipping Method'),
        //    'value'=>'$data->getShippingName()',
        //    'htmlOptions'=>array('style'=>'text-align:center;width:12%;'),
        //    'type'=>'html',
        //),
        //array(
        //    'name'=>'grand_total',
        //    'value'=>'$data->formatCurrency($data->grand_total)',
        //    'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
        //    'type'=>'html',
        //),
        //array(
        //    'name'=>'shop_id',//use shop_id as proxy for item name search
        //    'header'=>Sii::t('sii','Order Total'),
        //    'value'=>'$data->formatCurrency($data->getOrderTotal())',
        //    'htmlOptions'=>array('style'=>'text-align:center;width:8%'),
        //    'type'=>'html',
        //    'filter'=>false,                    
        //),
        array(
            'name'=>'status',
            'value'=>'Helper::htmlColorText($data->getStatusText())',
            'htmlOptions'=>array('style'=>'text-align:center;width:6%'),
            'type'=>'html',
            'filter'=>false,
        ),     
        array(
            'class'=>'SButtonColumn',
            'buttons'=>SButtonColumn::getOrderButtons(array(
                'view'=>true,
                'process'=>'$data->processable()',
                'verify'=>'$data->verifiable()',
                'refund'=>'$data->orderCancelled()',
            )),
            'template'=>'{view} {verify} {process} {refund}',
            'htmlOptions'=>array('style'=>'text-align:center;width:6%;'),
        ),
    ),
));