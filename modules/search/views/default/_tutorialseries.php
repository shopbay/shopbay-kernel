<?php
$this->widget('common.widgets.SDetailView', array(
    'data'=>$data->model,
    'htmlOptions'=>array('class'=>'list-box'),
    'attributes'=>array(
        array(
            'type'=>'raw',
            'template'=>'<div class="image">{value}</div>',
            'value'=>$data->model->getImageThumbnail(),
        ),        
        array(
            'type'=>'raw',
            'template'=>'{value}',
            'value'=>$this->widget('common.widgets.SDetailView', array(
                'data'=>$data->model,
                'htmlOptions'=>array('class'=>'data'),
                'attributes'=>array(
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="heading-element">{value}</div>',
                        'value'=>CHtml::link(CHtml::encode($data->model->name), $data->model->url),
                    ),
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="element">{value}</div>',
                        'value'=>'<span class="avatar">'.$data->model->author->getAvatar(Image::VERSION_XXXSMALL).'</span>'.
                                 CHtml::encode($data->model->author->nickname),
                    ),
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="element">{value}</div>',
                        'value'=>'<strong>'.CHtml::encode($data->model->getAttributeLabel('tags')).'</strong>'.
                                 ($data->model->hasTags()?Helper::htmlList($data->model->parseTags(),array('class'=>'tags')):Sii::t('sii','not set')),
                    ),        
                ),
            ),true),
        ),                
    ),
));