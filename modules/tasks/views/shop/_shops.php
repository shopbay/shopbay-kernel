<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'shop_grid',
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
            'class' =>$this->getModule()->getClass('imagecolumn'),
            'header'=>Sii::t('sii','Logo'),
            'name' => 'imageOriginalUrl',
            'htmlOptions'=>array('style'=>'text-align:center;width:60px;'),
        ),
        array(
            'name' =>'name',
            'value' =>'$data->displayLanguageValue(\'name\',user()->getLocale())',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),                 
        //array(
        //   'name'=>'tagline',
        //   'value' =>'$data->displayLanguageValue(\'tagline\',user()->getLocale())',
        //   'htmlOptions'=>array('style'=>'text-align:center;width:25%'),
        //   'type'=>'html',
        //),
        //array(
        //   'name'=>'create_time',
        //   'header'=>Sii::t('sii','Application Date'),
        //   'value'=>'$data->formatDateTime($data->create_time,true)',
        //   'htmlOptions'=>array('style'=>'text-align:center'),
        //   'type'=>'html',
        //),
        //array(
        //    'name'=>'account.name',
        //    'header'=>Sii::t('sii','Applicant'),
        //    'value'=>'$data->account->name',
        //    'htmlOptions'=>array('style'=>'text-align:center;width:8%;'),
        //),
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
                    'visible'=>'$data->operational()',
                ),
                'approve' => array(
                    'label'=>'<i class="fa fa-check" title="'.Sii::t('sii','Approve Shop').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'\'javascript:void(0)\'', 
                    'click'=>'function(){ws($(this));}',
	                'options'=>array(
        	            'data-action'=>WorkflowManager::ACTION_APPROVE,//for tasks.js use
            	    ),
                    'visible'=>'$data->pendingApproval()',
                ), 
             ),
            'template'=>'{view} {approve}',
            'htmlOptions' => array('style'=>'text-align:center;width:5%'),
        ),
    ),
));