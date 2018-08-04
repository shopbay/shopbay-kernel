<div class="list-box float-image">
    <div class="image">
       <?php echo $data->getObjectThumbnail();?>
    </div>
    <?php $this->widget('common.widgets.SDetailView', array(
            'data'=>$data,
            'htmlOptions'=>array('class'=>'data'),
            'attributes'=>array(
                array(
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>CHtml::link(LikeForm::getIcon(), 
                                         'javascript:void(0);',
                                         array('title'=>Sii::t('sii','Dislike {object}',array('{object}'=>ucfirst($data->displayLanguageValue('obj_name',user()->getLocale())))),
                                               'style'=>'vertical-align: bottom;cursor:pointer;',
                                               'onclick'=>'dislike('.$data->id.');')).
                             CHtml::link($data->displayLanguageValue('obj_name',user()->getLocale()),$data->obj_url),
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<span class="like-counter">'.CHtml::encode(Sii::t('sii','n<=1#{n} Like|n>1#{n} Likes',[$data->counter])).'</span>',
                ),        
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>Helper::prettyDate($data->update_time),
                ),        
            ),
        )); 
    ?>     
</div>