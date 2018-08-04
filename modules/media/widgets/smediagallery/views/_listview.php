<div class="list-box" data-media="<?php echo $data->id;?>">
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
                    'value'=>'<strong>'.Helper::rightTrim($data->name,15).'</strong>',
                ],
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>Helper::formatBytes($data->size),
                ],

            ],
        ]); 
    ?> 
</div>
