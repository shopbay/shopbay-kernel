<?php 
$this->widget('common.widgets.SDetailView', array(
    'data'=>$model,
    'columns'=>array(
        array(
            array('label'=>$model->getAttributeLabel('last_shop'),'type'=>'raw','value'=>$model->customerData->hasShopData()?$model->lastShopLink:Sii::t('sii','not available')),
            array('label'=>$model->getAttributeLabel('last_order'),'type'=>'raw','value'=>$model->hasCustomerData()?$model->lastOrderLink:Sii::t('sii','not available')),
            array('label'=>$model->getAttributeLabel('total_orders'),'type'=>'raw','value'=>$model->hasCustomerData()?$model->totalOrders:Sii::t('sii','not available')),
        ),
        array(
            array('name'=>'tags','type'=>'raw','value'=>$model->hasTags()?Helper::htmlList($model->parseTags(),array('class'=>'tags')):Sii::t('sii','not set')),
            array('name'=>'notes','type'=>'raw','value'=>$model->notes),
        ),
    ),
));