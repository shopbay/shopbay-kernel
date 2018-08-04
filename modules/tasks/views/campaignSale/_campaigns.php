<?php
$this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'campaign-sale-grid',
    'dataProvider'=>$dataProvider,
    //'filter'=>$searchModel,
    'columns'=>array(
        array(
            'class'=>'CCheckBoxColumn',
            'id'=>'task-checkbox',
            'name'=>'id',
            'selectableRows'=>'2',
            'htmlOptions'=>array('style'=>'text-align:center;width:3%'),
        ),
        array(
            'name' =>'name',
            'class' =>$this->getModule()->getClass('itemcolumn'),
            'label' => Sii::t('sii','Name'),
            'value' => '$data->getNameColumnData()',
            'type'=>'html',
            'htmlOptions'=>array('style'=>'text-align:center;width:47%'),
            ),
        array(
           'name' =>'start_date',
           'value'=>'$data->formatDatetime($data->start_date)',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%'),
         ),
        array(
           'name' =>'end_date',
           'value'=>'$data->formatDatetime($data->end_date)',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%'),
         ),
        array(
            'name'=>'status',
            'value'=>'Helper::htmlColorText($data->getStatusText())',
            'htmlOptions'=>array('style'=>'text-align:center;width:10%'),
            'type'=>'html',
            'filter'=>false,
        ),
        array(
            'class'=>'CButtonColumn',
            'buttons'=> array (
                'view' => array(
                    'label'=>'<i class="fa fa-info-circle" title="'.Sii::t('sii','More information').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'$data->viewUrl', 
                ),
             ),
            'template'=>'{view}',
            'htmlOptions' => array('style'=>'text-align:center;width:10%'),
        ),
    ),
));
