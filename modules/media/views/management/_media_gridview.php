<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>$scope,
    'dataProvider'=>$this->getDataProvider($scope, $searchModel),
    'viewOptionRoute'=>$viewOptionRoute,
    //'filter'=>$searchModel,
    'columns'=>array(
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
            'htmlOptions'=>array('width'=>'10%','style'=>'text-align:center;'),
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
                'delete' => array(
                    'label'=>'<i class="fa fa-trash-o" title="'.Sii::t('sii','Delete {object}',array('{object}'=>$searchModel->displayName())).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->deletable()', 
                    'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete').' "+$(this).parent().parent().children(\':first-child\').text()+"?")) return false;}',  
                ),                                    
            ),
            'template'=>'{view} {delete}',
            'htmlOptions'=>array('width'=>'8%'),
        ),
    ),
)); 