<?php $this->renderPartial('common.modules.notifications.templates.message.item._details',[
        'model'=>$model,
    ]); 
?>
<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Your return request has been rejected.');?>
    <?php if ($model->getTransitionCondition()!=null):?>
        <?php echo $model->getTransitionCondition()->message;?>
    <?php endif;?>
</p>

<?php $this->renderPartial('common.modules.notifications.templates.message.shop._returnpolicy',[
        'model'=>$model->shop,
    ]); 
?>

<?php $this->renderPartial('common.modules.notifications.templates.message.item._footer',
            array('status'=>$model->status,
                'itemUrl'=>Notification::getActionUrl($model),
                'orderUrl'=>Notification::getActionUrl($model->order))
            );