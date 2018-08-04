<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'tutorial_grid',
    'dataProvider'=>$dataProvider,
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
                    'label'=>'<i class="fa fa-level-up" title="'.Sii::t('sii','Submit Tutorial').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'\'javascript:void(0)\'', 
                    'click'=>'function(){wt($(this));}',
	                'options'=>array(
        	            'data-action'=>WorkflowManager::ACTION_SUBMIT,//for tasks.js use
            	    ),
                    'visible'=>'$data->submitable()',
                ), 
                'publish' => array(
                    'label'=>'<i class="fa fa-share-square-o" title="'.Sii::t('sii','Publish Tutorial').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'\'javascript:void(0)\'', 
                    'click'=>'function(){wt($(this));}',
	                'options'=>array(
        	            'data-action'=>WorkflowManager::ACTION_PUBLISH,//for tasks.js use
            	    ),
                    'visible'=>'$data->publishable()',
                ),                 
            ),
            'template'=>'{view} {submit} {publish}',
            'htmlOptions' => array('style'=>'text-align:center;width:10%'),
        ),
    ),
));
