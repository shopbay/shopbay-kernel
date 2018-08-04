<?php
/* @var $this ManagementController */
/* @var $data Comment */
?>
<?php if ($data->hasTarget()):?>
<div class="list-box float-image">
    
    <?php if ($data->rating!=null):?>
    <div class="status" style="margin:5px 15px;">
        <?php $this->widget('CStarRating',
                array('name'=>'rating'.$data->id,
                      'readOnly'=>true,
                      'value'=>$data->rating,
                      'htmlOptions'=>array('class'=>'star-rating'))
            );
        ?>
    </div>
    <?php endif;?>
    
    <div class="image">
        <?php echo $data->getTarget()->getImageThumbnail(Image::VERSION_SMEDIUM);?>
    </div>
    <?php $this->widget('common.widgets.SDetailView', array(
            'data'=>$data,
            'htmlOptions'=>array('class'=>'data'),
            'attributes'=>array(
                array(
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>CHtml::link(CHtml::encode($data->getTarget()->displayLanguageValue('name',user()->getLocale())), $data->getTarget()->url),
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>Helper::prettyDate($data->create_time),
                ),        
            ),
        )); 
    
        $this->widget('common.widgets.SDetailView', array(
            'data'=>$data,
            'htmlOptions'=>array('class'=>'data content'),
            'attributes'=>array(
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<i class="fa fa-quote-left"></i><div class="review rounded">'.l(Helper::purify($data->content),$data->viewUrl).'</div><i class="fa fa-quote-right"></i>',
                ),        
            ),
        )); 
    ?> 
</div>
<?php else:?>
<div class="list-box float-image">
    <p style="padding: 0px 10px;">
        <?php echo Sii::t('sii','No results found.');?>
    </p>
</div>
<?php endif;?>
