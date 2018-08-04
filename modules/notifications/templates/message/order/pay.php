<?php $this->renderPartial('common.modules.notifications.templates.message.order._details',[
        'model'=>$model,
        'showPaymentMethodName'=>true,
        'showPaymentMethodDesc'=>true,
    ]); 
?>

<p style="font-size:1.1em">
    <?php echo Sii::t('sii','{link} to track the status progress of your order and purchased items.',array(
                '{link}'=>CHtml::link(Sii::t('sii','Click here'),Notification::getActionUrl($model))));
    ?>
</p>
