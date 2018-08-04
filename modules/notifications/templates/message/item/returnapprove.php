<?php $this->renderPartial('common.modules.notifications.templates.message.item._details',[
        'model'=>$model,
    ]); 
?>
<p style="font-size:1.1em">
    <?php echo Sii::t('sii','We are glad to inform you that your return request for this item has been approved.');?>
    <?php echo Sii::t('sii','We will contact you to arrange refund matter shortly.');?>
</p>

<?php $this->renderPartial('common.modules.notifications.templates.message.shop._refundpolicy',[
        'model'=>$model->shop,
    ]); 
?>

<?php $this->renderPartial('common.modules.notifications.templates.message.item._footer',
            array('status'=>$model->status,
                'itemUrl'=>Notification::getActionUrl($model),
                'orderUrl'=>Notification::getActionUrl($model->order))
            );