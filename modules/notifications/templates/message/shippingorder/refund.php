<?php $this->renderPartial('common.modules.notifications.templates.message.shippingorder._details',[
        'model'=>$model,
        'showRefundDetails'=>true,
    ]); 
?>

<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Thanks for your support!');?>
</p>

<?php $this->renderPartial('common.modules.notifications.templates.message.shop._refundpolicy',[
        'model'=>$model->shop,
    ]); 