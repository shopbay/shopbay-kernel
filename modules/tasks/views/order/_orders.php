<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'dataProvider' => $dataProvider,
    //'filter'=>$searchModel,
    'columns'=>array(
        array(
           'name'=>'create_time',
           'value'=>'$data->formatDateTime($data->create_time,true)',
           'htmlOptions'=>array('style'=>'text-align:center;width:12%;'),
        ),
        array(
           'name'=>'order_no',
           'header'=> Sii::t('sii','Purchase Order'),
           'value'=>'$data->order_no',
           'value'=>'\'<div>\'.$data->order_no.\'</div><div>\'.$data->formatCurrency($data->grand_total).\'</div>\'',
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
        //    'name'=>'grand_total',
        //    'value'=>'$data->formatCurrency($data->grand_total)',
        //    'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
        //    'type'=>'html',
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
                'contact'=>'Yii::app()->controller->module->runAsBuyer',//only buyer can see this button
                'pay'=>'$data->payable()',
                'repay'=>'$data->repayable()',
                'verify'=>'$data->verifiable()',
            )),
            'template'=>'{view} {contact} {pay} {repay} {verify}',
            'htmlOptions'=>array('style'=>'text-align:center;width:10%;'),
        ),       
    ),
));