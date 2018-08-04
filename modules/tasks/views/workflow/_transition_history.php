<?php 
$this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'transition_history_grid',
    'dataProvider'=>$dataProvider,
    'template'=>'{items}',
    'columns'=>array(
        array(
           'name'=>'process_from',
           'value'=>'Helper::htmlColorText(Process::getDisplayTextWithColor($data->process_from))',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
         ),
        array(
           'name'=>'process_to',
           'value'=>'Helper::htmlColorText(Process::getDisplayTextWithColor($data->process_to))',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
         ),
        array(
           'name'=>'condition1',
           'header'=>Sii::t('sii','Description'),
           'value'=>'$data->isViewable(user())?$data->message:\'\'',//shows message only when user is the person executes process
           'type'=>'html',
        ),
        array(
            'name'=>'account.name',
            'header'=>Sii::t('sii','Processed By'),
            'value'=>'$data->account->name',
            'htmlOptions'=>array('style'=>'text-align:center'),
            'type'=>'html',
        ),
        array(
           'name'=>'transition_time',
           'header'=>Sii::t('sii','Process Time'),
           'value'=>'$data->account->formatDateTime($data->transition_time,true)',
           'htmlOptions'=>array('style'=>'text-align:center'),
           'type'=>'html',
         ),
    ),
)); 
