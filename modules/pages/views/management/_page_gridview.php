<?php $this->widget($this->getModule()->getClass('gridview'), [
    'id'=>$scope,
    'dataProvider'=>$this->getDataProvider($scope, $searchModel),
    'viewOptionRoute'=>$viewOptionRoute,
    //'filter'=>$searchModel,
    'columns'=> [
        [
            'name' =>'name',
            'value' => '$data->displayLanguageValue(\'title\',user()->getLocale())',
        ],
        [
            'name'=>'status',
            'value'=>'Helper::htmlColorText($data->getStatusText())',
            'htmlOptions'=>['style'=>'text-align:center;width:10%'],
            'type'=>'html',
            'filter'=>false,
        ],
        [
            'class'=>'CButtonColumn',
            'buttons'=> [
                'layout' => [
                    'label'=>'<i class="fa fa-columns" title="'.Sii::t('sii','Edit Page Layout').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'$data->layoutUrl', 
                ],  
                'view' => [
                    'label'=>'<i class="fa fa-info-circle" title="'.Sii::t('sii','More information').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'$data->viewUrl', 
                ],  
                'update' => [
                    'label'=>'<i class="fa fa-edit" title="'.Sii::t('sii','Update {object}',['{object}'=>$searchModel->displayName()]).'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'$data->updateUrl', 
                    'visible'=>'$data->updatable()', 
                ],                                    
                'delete' => [
                    'label'=>'<i class="fa fa-trash-o" title="'.Sii::t('sii','Delete {object}',['{object}'=>$searchModel->displayName()]).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->deletable()', 
                    'url'=>'$data->deleteUrl', 
                    'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete').' "+$(this).parent().parent().children(\':first-child\').text()+"?")) return false;}',  
                ],                                    
            ],
            'template'=>'{layout} {view} {update} {delete}',
            'htmlOptions'=>['width'=>'10%'],
        ],
    ],
]); 