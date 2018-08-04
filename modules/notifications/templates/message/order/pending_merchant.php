<?php $this->renderPartial('common.modules.notifications.templates.message.order._details',[
        'model'=>$model,
        'showPaymentMethodName'=>true,
        'domain'=>app()->urlManager->merchantDomain,
    ]); 
?>

<p style="font-size:1.1em">
    <?php   echo Sii::t('sii','{link} to process order.',[
                '{link}'=>CHtml::link(Sii::t('sii','Click here'),Notification::getActionUrl($model,app()->urlManager->merchantDomain))
            ]);
    ?>
</p>