<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>$scope,
    'dataProvider'=>$this->getDataProvider($scope, $searchModel),
    'viewOptionRoute'=>$viewOptionRoute,
    //'filter'=>$searchModel,
    'columns'=>array(
        array(
            'name' =>'obj_type',
            'class' =>$this->getModule()->getClass('itemcolumn'),
            'label' => Sii::t('sii','Comment On'),
            'value' => '$data->getTargetColumnData(user()->getLocale())',
            'htmlOptions'=>array('style'=>'text-align:center;width:30%'),
        ),                
        array(
            'name' =>'content',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'value'=>'Helper::purify($data->content)',
            'type'=>'html',
        ),
        array(
            'name' =>'create_time',
            'value' => '$data->formatDatetime($data->create_time,true)',
            'htmlOptions'=>array('style'=>'text-align:center;width:15%'),
            'type'=>'html',
        ),
        array(
            'class'=>'CButtonColumn',
            'buttons'=> array (
                'view' => array(
                    'label'=>'<i class="fa fa-info-circle" title="'.Sii::t('sii','More Information').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'$data->viewUrl', 
                ),                                    
                'update' => array(
                    'label'=>'<i class="fa fa-edit" title="'.Sii::t('sii','Update {object}',array('{object}'=>$searchModel->displayName())).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->updatable()', 
                ),                                    
                'delete' => array(
                    'label'=>'<i class="fa fa-trash-o" title="'.Sii::t('sii','Delete {object}',array('{object}'=>$searchModel->displayName())).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->deletable()', 
                    'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete comment for').' "+$(this).parent().parent().children(\':first-child\').text()+"?")) return false;}',  
                ),                                    
            ),
            'template'=>'{view} {update} {delete}',
            'htmlOptions'=>array('style'=>'text-align:center;width:8%'),
        ),
    ),
));