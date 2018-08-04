<?php $this->widget($this->getModule()->getClass('gridview'), array(
    'id'=>'history_grid',
    'dataProvider'=>$dataProvider,
    'template'=>'{items}',//'{items}{pager}'
    'columns'=>array(
        array(
           'name'=>'transition_time',
           'value'=>'$data->account->formatDateTime($data->transition_time,true)',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
           'type'=>'html',
        ),
        array(
            'name'=>'account.name',
            'header'=>Sii::t('sii','Processed By'),
            'value'=>'$data->getProcessedBy(user())',
            'htmlOptions'=>array('style'=>'text-align:center;width:10%;'),
            'type'=>'html',
        ),
        array(
           'name'=>'action',
           'value'=>'Process::getActionText($data->action)',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
           'type'=>'html',
        ),
//        array(
//           'name'=>'process_from',
//           'value'=>'Helper::htmlColorText(Process::getDisplayTextWithColor($data->process_from))',
//           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
//           'type'=>'html',
//        ),
        array(
           //'name'=>'process_to',
           'header'=>Sii::t('sii','Status'),
           'value'=>'Helper::htmlColorText(Process::getDisplayTextWithColor($data->process_to))',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
           'type'=>'html',
        ),
        array(
           'name'=>'condition1',
           'header'=>false,
           'value'=>'$data->isViewable(user())?$data->message:\'\'',//shows message only when user is the person executes process
           'type'=>'html',
        ),
    ),
)); 