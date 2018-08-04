<?php $this->widget($this->getModule()->getClass('gridview'), [
    'id'=>'history_grid',
    'dataProvider'=>$dataProvider,
    'template'=>'{items}',//'{items}{pager}'
    'columns'=>[
        [
           'name'=>'transition_time',
           'value'=>'$data->account->formatDateTime($data->transition_time,true)',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
           'type'=>'html',
        ],
        [
            'name'=>'account.name',
            'header'=>Sii::t('sii','Processed By'),
            'value'=>'$data->getProcessedBy(user())',
            'htmlOptions'=>array('style'=>'text-align:center;width:10%;'),
            'type'=>'html',
        ],
        [
           'name'=>'action',
           'value'=>'Process::getActionText($data->action)',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
           'type'=>'html',
        ],
//       [
//           'name'=>'process_from',
//           'value'=>'Helper::htmlColorText(Process::getDisplayTextWithColor($data->process_from))',
//           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
//           'type'=>'html',
//       ],
        [
           //'name'=>'process_to',
           'header'=>Sii::t('sii','Status'),
           'value'=>'Helper::htmlColorText(Process::getDisplayTextWithColor($data->process_to))',
           'htmlOptions'=>array('style'=>'text-align:center;width:15%;'),
           'type'=>'html',
        ],
        [
           'name'=>'condition1',
           'header'=>false,
           'value'=>'$data->isViewable(user())?$data->message:\'\'',//shows message only when user is the person executes process
           'type'=>'html',
        ],
    ],
]); 