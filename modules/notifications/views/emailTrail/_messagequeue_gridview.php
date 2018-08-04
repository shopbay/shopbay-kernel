<?php $this->widget($this->getModule()->getClass('gridview'), [
    'id'=>$scope,
    'dataProvider'=>$this->getDataProvider($scope, $searchModel),
    'viewOptionRoute'=>$viewOptionRoute,
    //'filter'=>$searchModel,
    'columns'=>[
        'id',
        [
            'header'=>Sii::t('sii','Send Mode'),
            'value'=>'$data->data->mode',
            'htmlOptions'=>['style'=>'text-align:center;'],
            'type'=>'html',
        ],
        [
            'header'=>Sii::t('sii','Address To'),
            'value'=>'$data->data->addressTo',
            'htmlOptions'=>['style'=>'text-align:center;'],
            'type'=>'html',
        ],
        [
            'header'=>Sii::t('sii','Address Name'),
            'value'=>'$data->data->addressName',
            'htmlOptions'=>['style'=>'text-align:center;'],
            'type'=>'html',
        ],
        [
            'header'=>Sii::t('sii','Subject'),
            'value'=>'$data->data->subject',
            'htmlOptions'=>['style'=>'text-align:center;'],
            'type'=>'html',
        ],
        [
            'name'=>'create_time',
            'value'=>'date(\'Y-m-d h:i:s\',$data->create_time)',
            'htmlOptions'=>['style'=>'text-align:center;'],
            'type'=>'html',
        ],
        [
            'name'=>'status',
            'value'=>'Helper::htmlColorText($data->getHtmlStatusTag())',
            'htmlOptions'=>['style'=>'text-align:center;'],
            'type'=>'html',
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
            'htmlOptions'=>['width'=>'5%'],
        ],
    ],
]);