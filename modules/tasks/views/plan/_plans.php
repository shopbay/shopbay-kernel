<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'plan_grid',
    'dataProvider'=>$dataProvider,
    //'filter'=>$searchModel,
    'columns'=>array(
        array(
           'name'=>'name',
           'htmlOptions'=>array('style'=>'text-align:center;'),
           'type'=>'html',
        ),
        array(
            'name'=>'type',
            'value'=>'$data->getTypeDesc()',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),
        array(
            'name'=>'recurring',
            'value'=>'$data->getRecurringDesc()',
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
                'submit' => array(
                    'label'=>'<i class="fa fa-level-up" title="'.Sii::t('sii','Submit Plan').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'\'javascript:void(0)\'', 
                    'click'=>'function(){wp($(this));}',
	                'options'=>array(
        	            'data-action'=>WorkflowManager::ACTION_SUBMIT,//for tasks.js use
            	    ),
                    'visible'=>'$data->submitable()',
                ), 
                'approve' => array(
                    'label'=>'<i class="fa fa-check" title="'.Sii::t('sii','Approve Plan').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'\'javascript:void(0)\'', 
                    'click'=>'function(){wp($(this));}',
	                'options'=>array(
        	            'data-action'=>WorkflowManager::ACTION_APPROVE,//for tasks.js use
            	    ),
                    'visible'=>'$data->approvable()',
                ), 
            ),
            'template'=>'{view} {submit} {approve}',
            'htmlOptions' => array('style'=>'text-align:center;width:10%'),
        ),
    ),
));
