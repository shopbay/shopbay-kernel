 <?php 
$this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'order_summary_grid',
    'dataProvider'=>$dataProvider,
    'template'=>'{items}',
    'columns'=>array(
        array(
           'name'=>'order_no',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
        array(
           'name'=>'create_time',
           'value'=>'$data->formatDatetime($data->create_time,true)',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
//        array(
//           'header'=> Sii::t('sii','Purchaser'),
//           'value'=>isset($customer)?'$data->account->name':'$data->order->account->name',
//           'htmlOptions'=>array('style'=>'text-align:center'),
//           'type'=>'html',
//           'visible'=>isset($customer)?false:true,
//        ),
        array(
           'header'=> Sii::t('sii','Payment Method'),
           'name'=>'payment_method',
           'value'=>isset($customer)?'$data->getPaymentMethodName(user()->getLocale())':'$data->order->getPaymentMethodName(user()->getLocale())',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
        array(
           'header'=>Sii::t('sii','Total Item'),
           'value'=>'$data->formatCurrency($data->item_total)',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
        array(
           'name'=>'discount',
           'value'=>'$data->formatCurrency($data->getDiscountTotal())',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'raw',
        ),
        array(
           'name'=>'tax',
           'value'=>isset($customer)?'$data->hasTax()?Helper::htmlList($data->getTaxDisplayText(user()->getLocale())):$data->formatCurrency(0)':'$data->order->hasTax()?Helper::htmlList($data->order->getTaxDisplayText(user()->getLocale())):Sii::t(\'sii\',\'not set\')',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'raw',
        ),
        array(
           'name'=>isset($customer)?'shipping_total':'item_shipping',
           'value'=>isset($customer)?'$data->formatShippingTotal($data->shipping_total,user()->getLocale())':'$data->formatShippingTotal($data->getShippingTotal(),user()->getLocale(),true)',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
         ),
        array(
            'name'=>'grand_total',
            'value'=>'$data->formatCurrency($data->grand_total)',
            'htmlOptions'=>array('style'=>'text-align:center'),
            'type'=>'html',
        ),
    ),
)); 
