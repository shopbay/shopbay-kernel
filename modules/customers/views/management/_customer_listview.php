<?php
/* @var $this ManagementController */
/* @var $data Customer */
?>
<div class="list-box float-image">
    <div class="image">
        <?php echo $data->getImageThumbnail();?>
    </div>
    <?php $this->widget('common.widgets.SDetailView', array(
            'data'=>$data,
            'htmlOptions'=>array('class'=>'data'),
            'attributes'=>array(
                array(
                    'type'=>'raw',
                    'template'=>'<div class="status">{value}</div>',
                    'value'=>$data->isRegistered?Helper::htmlColorText($data->getRegisteredText(),false):'',
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="heading-element">{value}</div>',
                    'value'=>CHtml::link(CHtml::encode($data->alias), $data->viewUrl),
                ),
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('last_shop')).'</strong>'.
                            ($data->customerData->hasShopData()?$data->lastShopLink:Sii::t('sii','not available')),
                ),        
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('last_order')).'</strong>'.
                             ($data->hasCustomerData()?$data->lastOrderLink:Sii::t('sii','not available')),
                ),        
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('total_orders')).'</strong>'.
                             ($data->hasCustomerData()?$data->totalOrders:Sii::t('sii','not available')),
                ),        
                array(
                    'type'=>'raw',
                    'template'=>'<div class="element">{value}</div>',
                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('tags')).'</strong>'.
                             ($data->hasTags()?Helper::htmlList($data->parseTags(),array('class'=>'tags')):Sii::t('sii','not set')),
                ),        
//                array(
//                    'type'=>'raw',
//                    'template'=>'<div class="element">{value}</div>',
//                    'value'=>'<strong>'.CHtml::encode($data->getAttributeLabel('notes')).'</strong>'.
//                             $data->notes,
//                ),        
            ),
        )); 
    ?> 
</div>