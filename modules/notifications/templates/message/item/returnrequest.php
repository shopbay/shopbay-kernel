<?php $this->renderPartial('common.modules.notifications.templates.message.item._details',[
        'model'=>$model,
    ]); 
?>
<p style="font-size:1.1em">
    <?php echo Sii::t('sii','You have item return request.');?>
</p>

<?php $this->renderPartial('common.modules.notifications.templates.message.item._footer',
            array('status'=>$model->status,
                'itemUrl'=>Notification::getActionUrl($model,app()->urlManager->merchantDomain),
                'orderUrl'=>Notification::getActionUrl($model->order,app()->urlManager->merchantDomain))
            );