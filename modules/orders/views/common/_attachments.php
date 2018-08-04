<?php $this->widget($this->getModule()->getClass('groupview'), array(
    'id'=>'attachment-grid',
    'dataProvider' => $dataProvider,
    'template'=>'{items}',
    'mergeColumns' => array('group'),  
    'columns'=>array(
        array(
           'name'=>'group',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
        array(
           'name'=>'name',
           'value'=>'Helper::htmlDownloadLink($data,Yii::app()->controller->getAssetsUrl(\'common.assets.images\'))',
           'htmlOptions'=>array('style'=>'padding-left:20px'),
           'type'=>'html',
        ),
        array(
           'name'=>'description',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
        array(
           'name'=>'size',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
        array(
           'name'=>'create_by',
           'value'=>'$data->account->name',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
        ),
    ),
));
