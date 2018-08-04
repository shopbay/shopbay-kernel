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
            'value'=>$data->model->getImageThumbnail(Image::VERSION_SMEDIUM),
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
                        'value'=>'<strong>'.CHtml::encode($data->model->getAttributeLabel('shop_id')).'</strong>'.
                                 CHtml::link(CHtml::encode($data->model->shop->displayLanguageValue('name',user()->getLocale())), $data->model->shop->url),
                    ),      
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="element">{value}</div>',
                        'value'=>'<strong>'.CHtml::encode($data->model->getAttributeLabel('unit_price')).'</strong>'.
                                 CHtml::encode($data->model->formatCurrency($data->model->unit_price)),
                    ),        
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="element">{value}</div>',
                        'value'=>'<strong>'.CHtml::encode(Sii::t('sii','Inventory')).'</strong>'.
                                 $data->model->getInventoryText(),
                    ),
                ),
            ),true),
        ),                
    ),
));