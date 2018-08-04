<?php  $this->widget($this->getModule()->getClass('gridview'), array(
        'id'=>'shipping_grid',
        'dataProvider'=>$dataProvider,
        //'filter'=>$searchModel,
        'columns'=>array(
            array(
                'class'=>'CCheckBoxColumn',
                'id'=>'task-checkbox',
                'name'=>'id',
                'selectableRows'=>'2',
                'htmlOptions'=>array('style'=>'text-align:center;width:3%'),
                'visible'=>isset($checkboxInvisible)?false:true,
            ),
            array(
               'header'=>Sii::t('sii','Shop'),
               'value'=>'$data->shop->displayLanguageValue(\'name\',user()->getLocale())',
               'htmlOptions'=>array('style'=>'text-align:center;width:20%'),
               'type'=>'html',
            ),                
            array(
               'name'=>'name',
               'value'=>'$data->displayLanguageValue(\'name\',user()->getLocale())',
               'htmlOptions'=>array('style'=>'text-align:center;width:30%'),
               'type'=>'html',
             ),
            array(
               'name'=>'method',
               'value'=>'$data->getMethodDesc()',
               'htmlOptions'=>array('style'=>'text-align:center;width:20%'),
               'type'=>'html',
             ),
            array(
               'name'=>'type',
               'value'=>'$data->getTypeDesc()',
               'htmlOptions'=>array('style'=>'text-align:center;width:20%'),
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
                'htmlOptions' => array('style'=>'text-align:center;width:5%'),
            ),
        ),    
    )); 
