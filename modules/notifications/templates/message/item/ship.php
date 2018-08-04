<?php $this->renderPartial('common.modules.notifications.templates.message.item._details',[
        'model'=>$model,
        'showShippingDetails'=>true,
    ]); 

    $this->renderPartial('common.modules.notifications.templates.message.item._footer',
            array('status'=>$model->status,
                'itemUrl'=>Notification::getActionUrl($model),
                'orderUrl'=>Notification::getActionUrl($model->order))
            );