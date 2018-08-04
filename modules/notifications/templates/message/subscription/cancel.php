<p style="font-size:1.1em">
    <?php echo Sii::t('sii','Subscription Details:');?>
</p>
<?php $this->renderPartial('common.modules.notifications.templates.message.subscription._subscription',array('model'=>$model));?>
<p style="font-size:1.1em">
    <?php echo Sii::t('sii','{link} to view details of this subscription.',array(
                '{link}'=>CHtml::link(Sii::t('sii','Click here'),Notification::getActionUrl($model))));
    ?>
</p>