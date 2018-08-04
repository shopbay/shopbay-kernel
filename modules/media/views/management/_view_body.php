<?php 
$this->widget('common.widgets.SDetailView', array(
    'data'=>$model,
    'columns'=>[
        [
            //['name'=>'filename','value'=> $model->filename],
            ['name'=>'create_time','value'=>date('Y-m-d h:i:s',$model->create_time)],
            ['name'=>'mime_type','value'=> $model->mime_type],
            ['name'=>'size','value'=> Helper::formatBytes($model->size)],
            ['label'=>Sii::t('sii','Attached To').' ('.count($model->distinctAssociations).')','type'=>'raw',
                        'value'=>$this->widget($this->module->getClass('listview'), array(
                                'dataProvider'=> new CArrayDataProvider($model->distinctAssociationObjects),
                                'template'=>'{items}',
                                'emptyText'=>'',
                                'itemView'=>'_association',
                            ),true)],
        ],
        [
            ['name'=>'src_url','value'=> $model->previewUrl,'visible'=>!$model->isExternalImage],
            ['name'=>'src_url','value'=> $model->src_url,'visible'=>$model->isExternalImage],
            ['label'=>'<i class="fa fa-download"></i> '.Sii::t('sii','Download Media'),'type'=>'raw','value'=>$model->downloadLink,'visible'=>!$model->isExternalImage],
        ],
    ],
));
     
$this->widget('common.widgets.SDetailView', array(
    'data'=>['preview'],
    'htmlOptions'=>['class'=>'media-preview-container'],
    'columns'=>[
        [
            ['label'=>Sii::t('sii','File Content'),'type'=>'raw','value'=> $model->preview],
        ],
    ],
));