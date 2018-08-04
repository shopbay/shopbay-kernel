<?php  $this->widget($this->getModule()->getClass('gridview'), array(
        'id'=>'product_grid',
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
               'name'=>'code',
               'value'=>'$data->code',
               'htmlOptions'=>array('style'=>'text-align:center;width:10%'),
               'type'=>'html',
             ), 
            array(
                'name' =>'name',
                'class' =>$this->getModule()->getClass('itemcolumn'),
                'label' => Sii::t('sii','Name'),
                'value' => '$data->getNameColumnData(user()->getLocale())',
            ),           
            array(
               'name'=>'unit_price',
               'value'=>'$data->formatCurrency($data->unit_price)',
               'htmlOptions'=>array('style'=>'text-align:center;width:15%'),
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
