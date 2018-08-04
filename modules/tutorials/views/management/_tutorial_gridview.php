<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>$scope,
    'dataProvider'=>$this->getDataProvider($scope, $searchModel),
    'viewOptionRoute'=>$viewOptionRoute,
    //'filter'=>$searchModel,
    'columns'=>array(
        array(
            'name'=>'name',
            'value'=>'$data->localeName(user()->getLocale())',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),
        array(
            'name'=>'difficulty',
            'value'=>'$data->getDifficultyText()',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),
        array(
            'name' =>'tags',
            'value'=>'$data->hasTags()?Helper::htmlList($data->parseTags(),array(\'class\'=>\'tags\')):Sii::t(\'sii\',\'not set\')',
            'htmlOptions'=>array('style'=>'text-align:center;width:30%'),
            'type'=>'html',
        ),
        array(
            'name'=>'status',
            'value'=>'Helper::htmlColorText($data->getStatusText())',
            'htmlOptions'=>array('width'=>'6%'),
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
                'update' => array(
                    'label'=>'<i class="fa fa-edit" title="'.Sii::t('sii','Edit {object}',array('{object}'=>$searchModel->displayName())).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->updatable()', 
                ),                                    
                'delete' => array(
                    'label'=>'<i class="fa fa-trash-o" title="'.Sii::t('sii','Delete {object}',array('{object}'=>$searchModel->displayName())).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->deletable()', 
                    'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete').' "+$(this).parent().parent().children(\':first-child\').text()+"?")) return false;}',  
                ),                                    
            ),
            'template'=>'{view} {update} {delete}',
            'htmlOptions'=>array('width'=>'8%'),
        ),
    ),
)); 