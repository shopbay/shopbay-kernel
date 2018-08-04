<?php 
$this->widget('common.widgets.SDetailView', [
    'data'=>$model,
    'columns'=>[
        [
            ['label'=>Sii::t('sii','Account Name'),'value'=>$model->account->name],
            ['name'=>'create_time','label'=>Sii::t('sii','Member since'),'value'=>$model->formatDatetime($model->account->create_time,true)],
            ['label'=>Sii::t('sii','Last update time'),'value'=>$model->formatDatetime($model->update_time,true)],
            ['label'=>Sii::t('sii','Email'),'value'=>$model->account->email],
            ['label'=>Sii::t('sii','Merchant Account'),'value'=>Sii::t('sii','Yes'),'visible'=>user()->hasRole(Role::MERCHANT)],
        ],
    ],
]);