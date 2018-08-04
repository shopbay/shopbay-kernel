<div class="list-box">
    <?php if ((isset($showStatus)&&$showStatus)||!isset($showStatus)):?>
    <span class="status">
        <?php echo Helper::htmlColorText($data['statusText'],false); ?>
    </span>    
    <?php endif;?>
    <?php 
        if (!(isset($currentTutorial)&&$currentTutorial==$data['id'])){
            $this->widget('common.widgets.SDetailView', array(
                'data'=>$data,
                'htmlOptions'=>array('class'=>'data'),
                'attributes'=>array(
                    array(
                        'type'=>'raw',
                        'template'=>'<div class="heading-element">{value}</div>',
                        'value'=>CHtml::link(CHtml::encode($data['name']), ((isset($showStatus)&&$showStatus)||!isset($showStatus))?$data['viewUrl']:$data['url'],['class'=>(isset($currentTutorial)&&$currentTutorial==$data['id'])?'active':'']),
                    ),
                ),
            )); 
        }

    ?> 
</div>
