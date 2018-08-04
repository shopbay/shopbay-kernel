<?php
$this->widget('common.widgets.SDetailView', array(
    'data'=>$data->model,
    'htmlOptions'=>array('class'=>'list-box float-image rounded'),
    'attributes'=>array(
//        array(
//            'type'=>'raw',
//            'template'=>'<div class="status">{value}</div>',
//            'value'=>Helper::htmlColorText($data->model->getStatusText(),false),
//        ),
        array(
            'type'=>'raw',
            'template'=>'<div class="image">{value}</div>',
            'value'=>$data->model->getImageThumbnail(Image::VERSION_ORIGINAL,array('style'=>'width:'.Image::VERSION_SMEDIUM.'px;')),
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
                        'value'=>CHtml::link(CHtml::encode($data->model->displayLanguageValue('name',user()->getLocale())), $data->model->url),
                    ),
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="element">{value}</div>',
                        'value'=>'<strong>'.CHtml::encode($data->model->getAttributeLabel('timezone')).'</strong>'.
                                 CHtml::encode(SLocale::getTimeZones($data->model->timezone)),
                    ),        
                ),
            ),true),
        ),                
    ),
));