<?php 
$this->widget($this->getModule()->getClass('groupview'), array(
    'id'=>'items-grid',
    'dataProvider'=>$dataProvider,
    //'filter'=>isset($searchModel)?$searchModel:null,
    //'mergeColumns' => array('order_no'),  
    'columns'=>array(
        //array(
        //   'name'=>'order_no',
        //   'value'=>'CHtml::link($data->order_no,$data->order->viewUrl)',
        //   'visible'=>isset($orderNoVisible)?true:false,
        //   'type'=>'raw',
        //   'htmlOptions'=>array('style'=>'text-align:center;width:10%;'),
        //),
        array(
           'name'=>'create_time',
           'value'=>'$data->formatDateTime($data->create_time,true)',
           'htmlOptions'=>array('style'=>'text-align:center;width:12%;'),
        ),
        array(
           'name'=>'shipping_order_no',
           'visible'=>isset($orderNoVisible)?true:false,
           'type'=>'raw',
           'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
        ),
        array(
            'name' =>'name',
            'class' =>$this->module->getClass('itemcolumn'),
            'label' => Sii::t('sii','Product'),
            'value' => '$data->getItemColumnData(user()->getLocale(),Yii::app()->controller->module->runAsBuyer,true)',
        ),
        array(
           'name'=>'unit_price',
           'type'=>'raw',
           'value'=>'Yii::app()->controller->widget(Yii::app()->controller->module->getClass(\'listview\'), 
                        array(
                            \'dataProvider\'=> $data->getPriceInfoDataProvider(user()->getLocale()),
                            \'template\'=>\'{items}\',
                            \'itemView\'=>Yii::app()->controller->module->getView(\'items.keyvalue\'),
                            \'emptyText\'=>\'\',
                            \'htmlOptions\'=>array(\'class\'=>\'price-details\'),
                        ),true)',
           'htmlOptions'=>array('style'=>'text-align:center;width:10%;'),
        ),
        //array(
        //   'name'=>'quantity',
        //    'htmlOptions'=>array('style'=>'text-align:center;width:5%;'),
        //),
        array(
            'header'=>Sii::t('sii','Discount'),
            'type'=>'html',
            'value'=>'$data->formatCurrency($data->orderDiscount).$data->getOrderDiscountTag(user()->getLocale())',
            'htmlOptions'=>array('style'=>'text-align:center;width:6%;'),
        ),
        array(
            'header'=>Sii::t('sii','Tax'),
            'value'=>'$data->formatCurrency($data->taxPrice)',
            'htmlOptions'=>array('style'=>'text-align:center;width:9%;'),
        ),
        array(
            'name'=>'total_price',
            'value'=>'$data->formatCurrency($data->grandTotal)',
            'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
        ),
        array(
            'name' =>'shipping_id',
            'header' => Sii::t('sii','Shipping'),
            'value' => '$data->shipping->displayLanguageValue(\'name\',user()->getLocale())',
           'htmlOptions'=>array('style'=>'text-align:center;'),
        ),
//        array(
//            'name'=>'account.name',
//            'header'=>Sii::t('sii','Purchase By'),
//            'value'=>'$data->account->name',
//            'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
//            'visible'=>isset($purchaserInfoInvisible)?false:true,
//        ),
        array(
            'name'=>'create_time',
            'header'=>Sii::t('sii','Purchase Date'),
            'value'=>'$data->formatDateTime($data->create_time,true)',
            'htmlOptions'=>array('style'=>'text-align:center;width:10%;'),
            'visible'=>isset($purchaserInfoInvisible)?false:true,
        ),
         array(
            'name'=>'status',
            'value'=>'Helper::htmlColorText($data->getStatusText())',
            'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
            'type'=>'html',
            'filter'=>false,
            'visible'=>isset($statusColumnInvisible)?false:true,                     
        ),
        array(
            'class'=>'SButtonColumn',
            'buttons'=> SButtonColumn::getItemButtons(array(
                //'view'=>true,//disabled as above item column already have item link
                'refund'=>'$data->refundable()',
                'review'=>'$data->reviewable()',
                'receive'=>'$data->receivable()',
                'return'=>'$data->returnable()',
                'process_item'=>'$data->shippable() && $data->oneStepWorkflow()',//this is for 1 step processing
                'ship'=>'$data->shippable() && $data->threeStepsWorkflow()',
                'pack'=>'$data->packable()',
                'pick'=>'$data->pickable()',
                'return'=>'$data->returnable()',
                'rollback'=>isset($customer)?'return false;':'$data->undoable()',
                'stockmanage'=>'$data->outOfStock()',
                ),isset($customer)?$customer:null
            ),
            'template'=>'{process_item} {rollback} {pick} {pack} {ship} {return} {stockmanage} {receive} {review} {refund}',
            'visible'=>isset($btnColumnInvisible)?false:true,
            'htmlOptions'=>array('style'=>'text-align:center;width:7%;'),
        ),
    ),
)); 