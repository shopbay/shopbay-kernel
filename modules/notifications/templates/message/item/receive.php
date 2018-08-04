<?php $this->renderPartial('common.modules.notifications.templates.message.item._details',[
        'model'=>$model,
    ]); 

    $this->renderPartial('common.modules.notifications.templates.message.item._footer',
            array('status'=>$model->status,
                'itemUrl'=>Notification::getActionUrl($model,app()->urlManager->merchantDomain),
                'orderUrl'=>Notification::getActionUrl($model->order,app()->urlManager->merchantDomain))
            );