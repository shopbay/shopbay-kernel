<?php
/* @var $this ManagementController */
/* @var $data Media */
?>
<div class="list-box">
    <span class="status">
        <?php echo Helper::htmlColorText($data->getStatusText(),false); ?>
    </span>
    <div class="preview">
        <?php echo $data->previewIcon;?>
    </div>    
    <?php $this->widget('common.widgets.SDetailView', [
            'data'=>$data,
            'htmlOptions'=>['class'=>'data'],
            'attributes'=> [
                [
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>CHtml::link(CHtml::encode($data->name), $data->viewUrl),
                ],
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('mime_type')).'</strong>'.
                            $data->mime_type,
                ],
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('size')).'</strong>'.
                            Helper::formatBytes($data->size),
                ],
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('create_time')).'</strong>'.
                            date('Y-m-d h:i:s',$data->create_time),
                ],
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.Sii::t('sii','Attached To').'</strong>'.
                            CHtml::encode(count($data->distinctAssociations)).
                            $this->widget($this->module->getClass('listview'),[
                                'dataProvider'=> new CArrayDataProvider($data->distinctAssociationObjects),
                                'template'=>'{items}',
                                'emptyText'=>'',
                                'itemView'=>'_association',
                            ],true),
                ],                  
            ],
        ]); 
    ?> 
</div>