<div class="list-box float-image">
    <div class="image">
        <?php echo $data->getObjectThumbnail();?>
    </div>
    <?php $this->widget('common.widgets.SDetailView', array(
            'data'=>$data,
            'htmlOptions'=>array('class'=>'data','style'=>'width:75%;'),
            'attributes'=>array(
                array(
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>CHtml::link($data->displayDescription(user()->getLocale()),$data->obj_url),
                    'visible'=>$data->showUrl(),
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>$data->displayDescription(user()->getLocale()),
                    'visible'=>!$data->showUrl(),
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<div class="summary">'.$data->summary.'</div>',
                ),        
            ),
        )); 
    ?> 
</div>
