<?php
/* @var $this ManagementController */
/* @var $data Tutorial */
?>
<div class="list-box">
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
                    'value'=>CHtml::link(CHtml::encode($data->localeName(user()->getLocale())), $data->viewUrl),
                ],
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('difficulty')).'</strong>'.
                             CHtml::encode($data->getDifficultyText()),
                ],
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('tags')).'</strong>'.
                             ($data->hasTags()?Helper::htmlList($data->parseTags(),['class'=>'tags']):Sii::t('sii','not set')),
                ],        
                [
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>CHtml::encode($data->url)
                ],              
            ],
        ]); 
    ?> 
</div>