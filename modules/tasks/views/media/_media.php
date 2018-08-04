<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'media_grid',
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
            'class' =>$this->getModule()->getClass('imagecolumn'),
            'header'=>Sii::t('sii','Image'),
            'name' => 'url',
            'htmlOptions'=>array('style'=>'text-align:center;width:60px;'),
        ),
        array(
            'name'=>'name',
            'value'=>'$data->name',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),
        array(
            'name'=>'mime_type',
            'value'=>'$data->icon.$data->mime_type',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),      
        array(
            'name'=>'size',
            'value'=>'Helper::formatBytes($data->size)',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),         
        array(
            'name'=>'create_time',
            'value'=>'date(\'Y-m-d h:i:s\',$data->create_time)',
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
                    //'label'=>'View', 
                    //'imageUrl'=>$this->getImage('view.png'),  
                    'url'=>'$data->viewUrl', 
                ),
             ),
            'template'=>'{view}',
            'htmlOptions' => array('style'=>'text-align:center;width:10%'),
        ),
    ),
));