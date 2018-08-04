<?php $this->renderPartial('common.modules.notifications.templates.message.shippingorder._details',[
        'model'=>$model,
    ]); 
?>

<p style="font-size:1.1em">
    <?php echo Sii::t('sii','{link} to process shipping order.',array(
                '{link}'=>CHtml::link(Sii::t('sii','Click here'),Notification::getActionUrl($model,app()->urlManager->merchantDomain)))
            );
    ?>
</p>