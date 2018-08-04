<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'ticket_grid',
    'dataProvider'=>$dataProvider,
    //'filter'=>$searchModel,
    'columns'=>array(
        array(
           'name'=>'account_id',
           'value'=>'$data->account->name',
           'htmlOptions'=>array('style'=>'text-align:center;'),
           'type'=>'html',
        ),
        array(
           'name'=>'subject',
           'htmlOptions'=>array('style'=>'text-align:center;'),
           'type'=>'html',
        ),
        array(
           'name'=>'create_time',
           'value'=>'$data->formatDatetime($data->create_time)',
           'htmlOptions'=>array('style'=>'text-align:center;'),
           'type'=>'html',
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
