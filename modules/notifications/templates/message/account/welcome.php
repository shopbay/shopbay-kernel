<div style="width:80%;">

    <?php echo Sii::t('sii','We are pretty certain you have started something really special, so we wanted to take a moment to personally welcome you to {app}.',['{app}'=>param('SITE_NAME')]);?>

    <?php if ($model->hasRole(Role::CUSTOMER) || isset($customerRole)): ?>

        <?php $this->renderPartial('common.modules.notifications.templates.message.account._welcome_customer');?>

    <?php else:?>

        <?php  $this->renderPartial('common.modules.notifications.templates.message.account._welcome_merchant'); ?>

    <?php endif;?>
    
</div>
    