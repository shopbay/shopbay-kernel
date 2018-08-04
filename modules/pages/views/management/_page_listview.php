<div class="list-box float-image">
    <span class="status">
        <?php echo Helper::htmlColorText($data->getStatusText(),false); ?>
    </span>
    <?php $this->widget('common.widgets.SDetailView', [
            'data'=>$data,
            'htmlOptions'=>['class'=>'data'],
            'attributes'=>[
                [
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>CHtml::link(CHtml::encode($data->displayLanguageValue('name',user()->getLocale())), $data->layoutUrl),
                ],
//                [
//                    'type'=>'raw',
//                    'template'=>'<div class="element">{value}</div>',
//                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('shop_id')).'</strong>'.
//                             CHtml::link(CHtml::encode($data->shop->displayLanguageValue('name',user()->getLocale())), $data->shop->viewUrl),
//                ],              
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>CHtml::encode($data->displayLanguageValue('desc',user()->getLocale())),
                ],              
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>CHtml::encode($data->url),
                    'visible'=>$data->updatable(),
                ],              
            ],
        ]); 
    ?> 
</div>