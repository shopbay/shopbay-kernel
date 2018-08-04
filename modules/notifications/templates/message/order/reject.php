<?php $this->renderPartial('common.modules.notifications.templates.message.order._details',['model'=>$model]); ?>

<p style="font-size:1.1em">
    <?php echo Sii::t('sii','We are unable to verify your payment by <strong>{payment_method}</strong>.',array('{payment_method}'=>$model->getPaymentMethodName(user()->getLocale())));?>
    <?php if ($model->getTransitionCondition()!=null):?>
        <?php echo $model->getTransitionCondition()->message;?>
    <?php endif;?>
    <?php echo Sii::t('sii','You may want to call your bank to check the possible causes.');?>
</p>
<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Or, if your payment amount is not correct, you can repay the order.');?>
</p>
<p style="font-size:1.1em">
    <?php echo Sii::t('sii','This order will not be processed for time being.');?>
</p>
