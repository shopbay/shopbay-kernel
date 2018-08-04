<?php  $this->widget($this->getModule()->getClass('gridview'), [
        'id'=>'pages_grid',
        'dataProvider'=>$dataProvider,
        //'filter'=>$searchModel,
        'columns'=> [
            [
                'class'=>'CCheckBoxColumn',
                'id'=>'task-checkbox',
                'name'=>'id',
                'selectableRows'=>'2',
                'htmlOptions'=>['style'=>'text-align:center;width:3%'],
                'visible'=>isset($checkboxInvisible)?false:true,
            ],
            [
               'header'=>Sii::t('sii','Shop'),
               'value'=>'$data->shop->displayLanguageValue(\'name\',user()->getLocale())',
               'htmlOptions'=>['style'=>'text-align:center;width:20%'],
               'type'=>'html',
            ],                
            [
               'name'=>'title',
               'value'=>'$data->displayLanguageValue(\'title\',user()->getLocale())',
               'htmlOptions'=>['style'=>'text-align:center;width:30%'],
               'type'=>'html',
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
                    'view' => [
                        'label'=>'<i class="fa fa-info-circle" title="'.Sii::t('sii','More information').'"></i>', 
                        'imageUrl'=>false,  
                        'url'=>'$data->viewUrl', 
                    ],
                ],
                'template'=>'{view}',
                'htmlOptions' => ['style'=>'text-align:center;width:5%'],
            ],
        ],    
    ]); 
