<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>$scope,
    'dataProvider'=>$this->getDataProvider($scope, $searchModel),
    'viewOptionRoute'=>$viewOptionRoute,
    'htmlOptions'=>array('data-description'=>$pageDescription,'data-view-option'=>$viewOption),
    //'filter'=>$searchModel,
    'columns'=>array(
        array(
            'value'=>'$data->isRegistered?$data->registeredTag:\'\'',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'raw',
        ),
        array(
            'name'=>'name',
            'value'=>'$data->alias',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),
        array(
            'header'=>Sii::t('sii','Last Visited Shop'),
            'value'=>'$data->customerData->hasShopData()?$data->lastShopLink:Sii::t(\'sii\',\'not available\')',
            'htmlOptions'=>array('style'=>'text-align:center;'),
            'type'=>'html',
        ),
        array(
            'header'=>Sii::t('sii','Last Order'),
            'value'=>'$data->hasCustomerData()?$data->lastOrderLink:Sii::t(\'sii\',\'not available\')',
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
            'class'=>'CButtonColumn',
            'buttons'=> array (
                'view' => array(
                    'label'=>'<i class="fa fa-info-circle" title="'.Sii::t('sii','More information').'"></i>', 
                    'imageUrl'=>false,  
                    'url'=>'$data->viewUrl', 
                ),  
                'update' => array(
                    'label'=>'<i class="fa fa-edit" title="'.Sii::t('sii','Update {object}',array('{object}'=>$searchModel->displayName())).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->updatable(user()->getId())', 
                ),                                    
                'delete' => array(
                    'label'=>'<i class="fa fa-trash-o" title="'.Sii::t('sii','Delete {object}',array('{object}'=>$searchModel->displayName())).'"></i>', 
                    'imageUrl'=>false,  
                    'visible'=>'$data->deletable(user()->getId())', 
                    'click'=>'js:function(){if (!confirm("'.Sii::t('sii','Are you sure you want to delete').' "+$(this).parent().parent().children(\':first-child\').text()+"?")) return false;}',  
                ),                                    
            ),
            'template'=>'{view} {update} {delete}',
            'htmlOptions'=>array('width'=>'8%'),
        ),
    ),
)); 